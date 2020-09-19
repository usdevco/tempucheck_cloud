<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['auth_mode']        			= 'oauth2';
$config['client_id']        			= 'ABPmARTaBGd1m8tc8vMwwIFGNZ6PBUaBFpD1Q74rA9UZwJrFGn';
$config['client_secret']    			= '9ghsrzS9GPBW4dts4jszQKppLjuxWP4rPcDIJs3g';
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
// $config['resource_owner_details']   	= 'https://developer.api.intuit.com/.well-known/openid_sandbox_configuration';
// $config['baseUrl']    					= 'Development';

// Production
$config['resource_owner_details']   = 'https://developer.api.intuit.com/.well-known/openid_configuration';
$config['baseUrl']    				= 'Production';
