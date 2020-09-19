<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['auth_mode']        			= 'oauth2';
$config['client_id']        			= 'ABhosyTMcTlMU7F2h1UFiwDZlewAEtuJtIIKp4cvRLPAYwHH1o';
$config['client_secret']    			= 'EII1mtFuwtdC1q9MzUElUcsyboMn2g15AfG73ILg';
$config['oauth_scope']    				= 'com.intuit.quickbooks.accounting openid profile email phone address';
// $config['redirect_uri']    				= 'https://mytempucheck.com/admin/invoices/list_invoices';
// $config['redirect_uri']    				= 'http://localhost/tempucheck-crm/admin/invoices/list_invoices';
// $config['redirect_uri']    				= 'https://mytempucheck.com/admin/invoices/callback';
$config['oauth_redirect_uri']    		= 'https://mytempucheck.com/admin/invoices/callback';
$config['authorizationRequestUrl']    	= 'https://appcenter.intuit.com/connect/oauth2';
$config['tokenEndPointUrl']    			= 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';
$config['urlAuthorize']    				= 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';
$config['urlAccessToken']				= 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer';

// Development
$config['resource_owner_details']   	= 'https://developer.api.intuit.com/.well-known/openid_sandbox_configuration';
// Production
//$config['resource_owner_details']   = 'https://developer.api.intuit.com/.well-known/openid_configuration';

$config['baseUrl']    					= 'Development';
// $config['baseUrl']    				= 'Production';