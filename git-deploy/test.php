<?php

define( 'ACTIVE_DEPLOY', true ) ;
define( 'DEPLOY_LOG_DIR', dirname( __FILE__ ) ) ;

//require_once('class.test.php');
require_once('class.githubdeployhook.php');

// Initiate the GitHub Deployment Hook; Passing true to enable debugging
$hook = new GitHubDeployHook(true);

$hook->addrepo( 'openleaf-co-nz', 'mysScretKey' );
$hook->addrefs( 'openleaf-co-nz', 'branchname', 'path');
$hook->addrefs( 'openleaf-co-nz', 'master', '/var/www/openleaf', DEPLOY_LOG_DIR . '/post-deploy.sh');
//$hook->addrefs( 'testrepo', 'tags', 'another/path');
//$hook->addrefs( 'testrepo', 'tags/*', 'another/path');
//$hook->addrefs( 'testrepo', 'tags/1*', 'another/path');
//$hook->addrefs( 'testrepo', 'develop/*', 'another/path');
$hook->addrefs( 'invalidrepo', 'master', 'another/path');
$hook->addrefs( 'invalidrepo', 'master', 'another/path');
//$hook->addrefs( 'testrepo', 'master', 'another/path');
// $hook->showrepos();
$hook->deploy();


/*
 *  define( 'ACTIVE_DEPLOY', deploy_boolean ); // must be set true to deploy
 *  define( 'DEPLOY_LOG_DIR', log_dir_path );  // set to preferred location
 *
 *  require_once('class.githubdeployhook.php');
 *
 *  $hook = new GitHubDeployHook( debug_boolean );
 *	$hook->addrepo( 'repo_name', 'secret_access_token', 'remote_repo_name' );
 *  $hook->addrefs( 'repo_name', 'branch_name', 'deployment_path', 'post_deployment_command' );
 *  $hook->deploy()
 */

/*
 *  TO CLONE: git clone git@github.com:USERNAME/REPO.git --single-branch --branch BRANCHNAME DIRECTORY
 *
 *
 *  TO CHECK: git status --branch
 *  returns   # On branch master		// first line of response
 *  
 *  TO CHECK: git remote -v
 *  returns   origin	git@github.com:openleaf/openleaf-co-nz.git (fetch)    // first line of response
 *
 *  TO CHECK: git remote
 *  returns   origin
 *

 */
