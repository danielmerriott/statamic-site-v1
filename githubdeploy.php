<?php

define( 'ACTIVE_DEPLOY', true ) ;
define( 'DEPLOY_LOG_DIR', '../../logs/git-deploy' ) ;

//require_once('class.test.php');
require_once(dirname( __FILE__ ) . '/lib/class.githubdeployhook.php');

// Initiate the GitHub Deployment Hook; Passing true to enable debugging
$hook = new GitHubDeployHook(true);

$hook->addrepo( 'openleaf-co-nz', 'jHJcbjTNTam' );
$hook->addrefs( 'openleaf-co-nz', 'master', '/data/www/openleaf-co-nz/htdocs/master', dirname( __FILE__ ) . '/lib/post-deploy.sh');
$hook->addrefs( 'openleaf-co-nz', 'tags/*', '/data/www/openleaf-co-nz/htdocs/live', dirname( __FILE__ ) . '/lib/post-deploy.sh');
$hook->addrefs( 'openleaf-co-nz', 'legacy', '/data/www/openleaf-co-nz/htdocs/legacy', dirname( __FILE__ ) . '/lib/post-deploy.sh');
$hook->addrefs( 'openleaf-co-nz', 'git-deploy', '/data/www/openleaf-co-nz/htdocs/git-deploy', dirname( __FILE__ ) . '/lib/post-deploy.sh');
$hook->deploy();

/*
 *  define( 'ACTIVE_DEPLOY', deploy_boolean ); // must be set true to deploy
 *  define( 'DEPLOY_LOG_DIR', log_dir_path );  // set to preferred location
 *
 *  require_once('lib/class.githubdeployhook.php');
 *
 *  $hook = new GitHubDeployHook( debug_boolean );
 *  $hook->addrepo( 'repo_name', 'secret_access_token', 'remote_repo_name' );
 *  $hook->addrefs( 'repo_name', 'branch_name', 'deployment_path', 'post_deployment_command' );
 *  $hook->deploy()
 */

