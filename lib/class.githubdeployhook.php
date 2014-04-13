<?php

// The follow should removed
error_reporting(0);

/**
 *
 */
class GitHubDeployHook {

    /**
     *  @var string 	The name of the file that will be used for logging deployments
     *  Set to false to disable logging.
     */
    private static $_log_name = 'deployhook.log';

    /**
     *  @var string 	The path to where we wish to store our log file
     */
    private static $_log_path = DEPLOY_LOG_DIR;

    /**
	 *	@var boolean 	Log/show debug messages
	 */
	private $_debug = false;

	/**
	 *  @var object 	Payload from GitHub
	 */
	private $_payload = '';

	/**
	 *  @var string 	Token parameter provided in URL
	 */
	private $_token = '';

	/**
	 *  @var string 	IP address of caller
	 */
	private $_remoteip = '';

	/**
	 *  @var array 		Repositories container
	 */
	private $_repos = '';


	/**
	 *	Class constructor.
	 *
	 *  @param boolean $debug 	Log/show debug messages
	 */
	function __construct( $debug = false) {

	    $this->_debug = $debug;

	    // Get and record IP address of requestor.
	    if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$this->_remoteip = $_SERVER['REMOTE_ADDR'];
			$this->log( 'Request received from ' . $this->_remoteip, 'DEBUG' );
		} else {
			$this->log( 'Request received with no remote IP address', 'DEBUG' );
		}

		// Check for valid deployment endpoint.
		if ( ! defined( 'ACTIVE_DEPLOY' ) || true !== ACTIVE_DEPLOY ) {
			$this->debug_and_die( 'Access Denied', 'Not a valid deployment endpoint' );
		}

		// Process payload.
		if ( isset( $_POST['payload'] )) {
			$this->_payload = json_decode( $_POST['payload'] );
		} else {
			$this->debug_and_die( 'Missing Payload', 'No payload found' );
		}

		// Process token parameter.
		if ( isset( $_GET['token'] )) {
			$this->_token = $_GET["token"];
		} else {
			$this->debug_and_die( 'Missing Auth Token', 'No auth token found' );
		}

	}

	/**
	 *	Provides feedback with hypertext if in debug mode, otherwise provides a 404 HTTP response.
	 *
	 *  @param string $title 	Response title
	 *  @param string $message 	Response message
	 */
	private function debug_and_die( $title, $message ) {
		if ( $this->_debug ) {
			$this->log( 'Die with message: ' . $title . ' - ' . $message, 'DEBUG' );
			die( '<h1>'.$title.'</h1><p>'.$message.'</p>' );
		} else {
			header( 'HTTP/1.1 404 Not Found' );
			die( '404 Not Found.' );
		}		
	}

	/**
	 *  Write to the log file.
	 *
	 *  @param string $message 	Message to log
	 *  @param string $type 	The type of log message (DEBUG, INFO, ERROR, WARNING, SUCCESS, FAILURE, etc)
	 */
    protected function log( $message, $type = 'INFO' ) {
    	// Only write DEBUG messages if $_debug is true
    	if ( ! ( ($type == 'DEBUG') && (! $this->_debug) ) ) {
	        if ( self::$_log_name ) {
	            // Set the name of the log file
	            $filename = self::$_log_path . '/' . date( 'Y-m') . '-' . self::$_log_name;
	            if ( ! file_exists( $filename ) ) {
	                // Create the log file
	                file_put_contents( $filename, '' );
	            }
	            // Write the message into the log file
	            // Format: time --- type: message
	            file_put_contents( $filename, date( 'Y-m-d H:i:sP' ) . ' --- ' . str_pad( $type.':',10 ) . $message . PHP_EOL, FILE_APPEND );
	        }
	    }
    }

    /**
     *  Add a repo to be processed.
     *
     *  @param string $name 	Repository name
     *  @param string $token 	Auth token string
     *  @param string $remote 	Remote repository to pull from, defaults to 'origin'
     */
    public function addrepo( $name, $token, $remote = 'origin' ) {
    	$name = strtolower( $name );
    	$remote = strtolower( $remote );
    	$this->log( 'Adding repo ' . $name . ' with token ' . $token, 'DEBUG' );
    	$this->_repos[$name] = array(
    		'token'     => $token,
    		'remote'    => $remote,
    		'refs'  => array()
    		);
    }

    /**
     *  Add a ref to an existing repo.
     *
     *  @param string $repo 	Repository name
     *  @param string $name 	Ref matching string
     *  @param string $path 	Deployment path
     *  @param string $post 	Post deployment script to run
     */
    public function addrefs( $repo, $name, $path, $post) {
    	$name = strtolower( $name );
    	if ( substr( $name, 0, 4 ) == 'tags' ) {
    		$refs = 'refs/'.$name;
    	} else {
    		$refs = 'refs/heads/'.$name;
    	}
    	if ( isset( $this->_repos[$repo] ) ) {
	    	$this->log( 'Adding ref ' . $refs . ' to repo ' . $repo, 'DEBUG' );
	    	$this->_repos[$repo]['refs'][$refs] = array(
	    		'path' => $path,
	    		'post' => $post
	    		);
    	} else {
    		$this->log( 'Unable to add ref ' . $refs . ' as repo ' . $repo . ' not declared', 'DEBUG' );
    	}
    }

    /**
     *  Attempt deployment of any repos found in the payload
     *  
     */
    public function deploy( $clean = false ) {
    	// get the available repo / token / ref / commit details.
    	$repo = strtolower( $this->_payload->repository->name );
    	$token = $this->_token;
    	$ref = strtolower( $this->_payload->ref );
    	$commit = substr( $this->_payload->after, 0, 10 );
    	$this->log( '[SHA:' . $commit . '] Request received from ' . $this->_remoteip . ' for ' . $repo . '/' . $ref, 'INFO' );
    	// check for a matching repo.
    	if ( isset( $this->_repos[$repo] ) ) {
    		$this->log( '[SHA:' . $commit . '] Matched ' . $repo, 'DEBUG' );
    		// check for matching token.
    		if ( $this->_repos[$repo]['token'] !== $token ) {
    			$this->log( '[SHA:' . $commit . '] Invalid token [' . $token . '] for repo ' . $repo, 'WARNING' );
    			$this->debug_and_die( 'Invalid Request', 'Invalid token [' . $token . '] for repo ' . $repo );
    		} else {
    			$this->log( '[SHA:' . $commit . '] Matched token ' . $token . ' for ' . $repo, 'DEBUG' );
    		}
    		// check for matching ref.
    		if ( isset( $this->_repos[$repo]['refs'][$ref] ) ) {
    			// Direct match found.
    			$this->log( '[SHA:' . $commit . '] Direct match for ref ' . $ref . ' in ' . $repo, 'DEBUG' );
    			// *** this is where we go do some actual deployment work!
    			$this->gitdeploy( $this->_repos[$repo]['refs'][$ref]['path'],
    							  $this->_repos[$repo]['remote'], 
    							  $ref,
    							  $this->_repos[$repo]['refs'][$ref]['post'],
    							  $commit,
    							  $repo,
							  $clean );
    		} else {
    			$this->log( '[SHA:' . $commit . '] No direct matches for ref ' . $ref . ' in ' . $repo, 'DEBUG' );
    			// Loop through stored refs and attempt match those ending in '*'.
    			$bestkey = '';
    			foreach ( $this->_repos[$repo]['refs'] as $key => $element ) {
    				if ( substr($key, -1) == '*') {
    					$len = strlen( $key ) - 1;
    					if ( substr( $ref, 0, $len ) == substr( $key, 0, $len ) ) {
    						$this->log( '[SHA:' . $commit . '] Potential match (' . $key . ') for ref ' . $ref . ' in ' . $repo, 'DEBUG' );
    						// keep the longest match (or the earlist match if equal length).
    						if ( ($len+1) > strlen( $bestkey ) ) {
    							$bestkey = $key;
    						}
    					}
    				}
    			}
    			if ( $bestkey == '' ) {
    				// Die if no matching ref found for repo.
    				$this->debug_and_die( 'Invalid Request', 'Undefined repo/branch/ref ' . $repo . '/' . $ref );
    			} else {
    				// Best match found.
    				$this->log( '[SHA:' . $commit . '] Best match (' . $bestkey . ') found for ref ' . $ref . ' in ' . $repo, 'DEBUG' );
    				// *** this is where we go do some actual deployment work!
	    			$this->gitdeploy( $this->_repos[$repo]['refs'][$bestkey]['path'],
	    							  $this->_repos[$repo]['remote'], 
	    							  $ref,
	    							  $this->_repos[$repo]['refs'][$bestkey]['post'],
	    							  $commit,
	    							  $repo,
								  $clean );
    			}
    		}
    	} else {
    		// Die if repo not found.
    		$this->log( '[SHA:' . $commit . '] Undefined repo/branch/ref ' . $repo . '/' . $ref, 'WARNING' );
    		$this->debug_and_die( 'Invalid Request', 'Undefined repo/branch/ref ' . $repo . '/' . $ref );
    	}

    }

    /**
     *  Perform the git deployment actions.
     * 
     *  @param string $path 	Deployment path
     *  @param string $remote 	Remote repo to pull from
     *  @param string $ref 		Remote reference to pull
     *  @param string $post 	Post deployment script to run
     *  @param string $commit 	Remote commit SHA (for logging purposes)
     *  @param string $repo 	Name of repo (for logging purposes)
     */
	private function gitdeploy( $path, $remote, $ref, $post, $commit, $repo, $clean ) {
		$this->log( '[SHA:' . $commit . '] Starting gitdeploy actions for ' . $repo . '/' . $ref, 'DEBUG' );
		try {
			// Resolve path / does path exist?
			$realpath = realpath( dirname( $path ) ) . '/' . basename( $path );
			if ( ! file_exists( $realpath ) ) {
				throw new Exception( 'Path [' . $path . '] can not be found' );
			} else {
				$this->log( '[SHA:' . $commit . '] Destination path ' . $realpath, 'DEBUG' );
			}
			// Check for .git sub-directory / is path under git control.
			if ( ! file_exists( $realpath . '/.git' ) ) {
				throw new Exception( 'Path [' . $realpath . '] not under git control' );
			}
			// Discard any changes to tracked files since our last deploy.
			$this->cmdexec( 'git --version', 'Git version' );
			// *** Add additional git checks. e.g. git remote / git --status branch
			// Make sure we're in the right directory.
			$return = chdir( $realpath );
			if ( ! $return ) {
				throw new Exception( 'Unable to chdir to ' . $realpath );
			}
			// Discard any changes to tracked files since our last deploy.
			$this->cmdexec( 'git reset --hard HEAD', 'Git reset' );
			// Discard any untracked files
			if ( $clean ) {
				$this->cmdexec( 'git clean -d --force', 'Git clean' );
			}
			// Update the local repository.
			unset ( $return );
			if ( substr( $ref, 0, 9 ) == 'refs/tags' ) {
				$this->log( '[SHA:' . $commit . '] XYZ ' . $ref , 'DEBUG' );
				$return = $this->cmdexec( 'git pull --tags ' . $remote . ' master' , 'Git pull (tags)' );
				if ( $return == true ) {
                                        $this->log( '[SHA:' . $commit . '] Deploy action for ' . $repo . '/' . $ref .' (pull tags) completed successfully. ' . $output[0] , 'SUCCESS' );
				}
				unset ( $return );
				$return = $this->cmdexec( 'git checkout ' . $ref  , 'Git checkout' );
                                if ( $return == true ) {
                                        $this->log( '[SHA:' . $commit . '] Deploy action for ' . $repo . '/' . $ref .' (checkout tag) completed successfully. ' . $output[0] , 'SUCCESS' );
                                }
			} else {
				$return = $this->cmdexec( 'git pull ' . $remote . ' ' . $ref , 'Git pull' );
				if ( $return == true ) {
					$this->log( '[SHA:' . $commit . '] Deploy action for ' . $repo . '/' . $ref .' completed successfully. ' . $output[0] , 'SUCCESS' );
				}
			}

			//exec( 'git checkout ' . $ref, $output );
			// Run post-deploy script.
			// Remember that any relative paths are resolved from the repo path as we chdir'd earlier
			if ( ! empty( $post ) ) {
				$realpost = realpath( dirname( $post ) ) . '/' . basename( $post );;
				$this->log( '[SHA:' . $commit . '] Post-deploy script found ' . $realpost, 'DEBUG' );
				if ( ! file_exists( $realpost ) ) {
					throw new Exception( 'Post-deploy script ' . $post . ' can not be found' );
				} else {
					unset ( $return );
					$return = $this->cmdexec( $realpost . ' ' . $path . ' ' . $remote . ' ' . $ref . ' ' . $commit . ' ' . $repo, 'Post-deploy script' );
					$this->log( '[SHA:' . $commit . '] Post-deploy action(s) for ' . $repo . '/' . $ref .' completed successfully.' , 'SUCCESS' );
				}
			} else {
				$this->log( '[SHA:' . $commit . '] Did not know what to do with post-deploy script ' . $post, 'DEBUG' );
			}
		} catch( Exception $e ) {
			$this->log( '[SHA:' . $commit . '] Deploy error - ' . $e->getMessage(), 'ERROR' );
			$this->log( '[SHA:' . $commit . '] Deploy action for ' . $repo . '/' . $ref .' failed. ' . $output[0] , 'FAILURE' );
		}
	}

	private function cmdexec( $cmd, $title ) {
		exec( $cmd . ' 2>&1', $output, $result );
		if ( ! $result == 0 ) {
			throw new Exception( $title . ' failed: ' . $output[0] );
			return false;
		} else {
			$this->log( '[SHA:' . substr( $this->_payload->after, 0, 10 ) . '] ' . $title . ' returned: ' . end($output) , 'DEBUG' );
			return true;
		}
	}
}

