<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define( 'OPENID_LIBRARY_ROOT', ROOT . "/lib/contrib/phpopenid/" );
define( 'XPATH_LIBRARY_ROOT', ROOT . "/lib/contrib/phpxpath/" );
define( 'Auth_OpenID_NO_MATH_SUPPORT', 1 );
define( 'OPENID_PASSWORD', "-OPENID-" );

$path_extra = dirname(__FILE__);
$path = ini_get('include_path');

if( !isset( $_ENV['OS'] ) || strstr( $_ENV['OS'], 'Windows' ) === false ) {
	$path .= ':' . OPENID_LIBRARY_ROOT . ':' . $path_extra;
} else {
	$path .= ';' . OPENID_LIBRARY_ROOT . ';' . $path_extra;
}
ini_set('include_path', $path);

requireComponent( "Eolin.PHP.Core" );
requireComponent( "Textcube.Core" );
requireComponent( "Textcube.Control.Auth" );
requireComponent( "Textcube.Function.misc" );

include_once OPENID_LIBRARY_ROOT."Auth/Yadis/XML.php";
include_once XPATH_LIBRARY_ROOT."XPath.class.php";

class Auth_Textcube_xmlparser extends XPath
{
	function Auth_Textcube_xmlparser()
	{
		$this->ns = array();
        $xmlOptions = array(XML_OPTION_CASE_FOLDING => false, XML_OPTION_SKIP_WHITE => TRUE);
        parent::XPath( FALSE, $xmlOptions );
        $this->bDebugXmlParse = false;
    }

    function init($xml_string, $namespace_map)
    {
        foreach ($namespace_map as $prefix => $uri) {
            if (!$this->registerNamespace($prefix, $uri)) {
                return false;
            }
        }
        if (!$this->setXML($xml_string)) {
            return false;
        }

        return true;
    }

    function setXML($xml_string)
    {
    	return $this->importFromString( $xml_string );
    }

    function evalXPath($xpath, $node = null)
    {
    	if( $xpath[0] != '/' ) { $xpath = "//$xpath"; }
    	$nodes = $this->evaluate($xpath);
    	$return_nodes = array();
    	foreach( $nodes as $n ) {
    		$node = $this->nodeIndex[$n];
    		$node['text'] = join( '', $node['textParts'] );
    		$return_nodes[] = $node;
    	}
    	return $return_nodes;
    }

    function content($node)
    {
		return $node['text'];
    }

    function attributes($node)
    {
        if (isset($node['attributes'])) {
				return $node['attributes'];
        }
		return null;
    }
}

class OpenID {
	function setCookie( $key, $value )
	{
		if( !headers_sent() ) {
			setcookie( $key, $value, time()+3600*24*30, "/" );
		}
	}

	function clearCookie( $key )
	{
		if( !headers_sent() ) {
			setcookie( $key, '', time()-3600, "/" );
		}
	}

}

class OpenIDSession {
	function OpenIDSession($tid) {
		$this->pickle_key = $tid;
	}

    function set($name, $value)
    {
		$tr = Transaction::taste( $this->pickle_key );
        $tr[$name] = $value;
		Transaction::repickle( $this->pickle_key, $tr );
    }

    function get($name, $default=null)
    {
		$tr = Transaction::taste( $this->pickle_key );
        if (array_key_exists($name, $tr)) {
            return $tr[$name];
        } else {
            return $default;
        }
    }

    function del($name)
    {
		$tr = Transaction::taste( $this->pickle_key );
        unset($tr[$name]);
		Transaction::repickle( $this->pickle_key, $tr );
    }

    function contents()
    {
		$tr = Transaction::taste( $this->pickle_key );
        return $tr;
    }
}

class OpenIDConsumer extends OpenID {
	function OpenIDConsumer($tid = null) {
		require_once OPENID_LIBRARY_ROOT."Auth/OpenID/Consumer.php";
		require_once OPENID_LIBRARY_ROOT."Auth/OpenID/FileStore.php";
		require_once OPENID_LIBRARY_ROOT."Auth/OpenID/SReg.php";

		$store_path = ROOT . "/cache/_php_consumer";

		if (!file_exists($store_path) &&
			!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
				" Please check the effective permissions.";
			exit(0);
		}

		$store = new Auth_OpenID_FileStore($store_path);

		/**
		 * Create a consumer object using the store object created earlier.
		 */
		if( $tid ) {
			$this->session = new OpenIDSession( $tid );
		} else {
			$this->session = null;
		}

		$this->consumer = new Auth_OpenID_Consumer($store, $this->session );
	}

	function fetch( $openid )
	{
		ob_start();
		$auth_request = $this->consumer->begin($openid);
		ob_end_clean();
		return $auth_request->endpoint->claimed_id;
	}

	function fetchXRDSUri( $openid )
	{
		global $TextCubeLastXRDSUri;
		$TextCubeLastXRDSUri = '';

		ob_start();
		$auth_request = $this->consumer->begin($openid);
		ob_end_clean();

		if (!$auth_request) {
			return array( '', '', '' );
		}

		if( $auth_request->endpoint->local_id ) {
			$IdPIdentity = $auth_request->endpoint->local_id; 
		} else {
			$IdPIdentity = $auth_request->endpoint->claimed_id; 
		}
		return array( 
			$IdPIdentity,
			$auth_request->endpoint->server_url, 
			$TextCubeLastXRDSUri );
	}

	function tryAuth( $tid, $openid, $remember_openid = null )
	{
		global $hostURL, $blogURL;
		$trust_root = $hostURL . "/";
		ob_start();
		$auth_request = $this->consumer->begin($openid);
		ob_end_clean();

		// Handle failure status return values.
		if (!$auth_request) {
			return $this->_redirectWithError( _text("인증하지 못하였습니다. 아이디를 확인하세요"), $tid );
		}

		if( ! $this->IsExisted( $auth_request->endpoint->claimed_id ) )
		{
			$sreg_request = Auth_OpenID_SRegRequest::build( null, array( 'nickname' ) );
			$auth_request->addExtension( $sreg_request );
		}

		if( $remember_openid ) {
				$this->setCookie( 'openid',
						empty($auth_request->endpoint->display_identifier) ?
						$auth_request->endpoint->claimed_id : $auth_request->endpoint->display_identifier );
		} else {
				$this->clearCookie( 'openid' );
		}

		$tr = Transaction::taste( $tid );
		$finishURL = $tr['finishURL'];
		$redirect_url = $auth_request->redirectURL($trust_root, $finishURL);

		return $this->redirect( $redirect_url );
	}

	function finishAuth( $tid )
	{
		global $hostURL, $blogURL;
		// Complete the authentication process using the server's response.
		$tr = Transaction::taste($tid);
		ob_start();
		$response = $this->consumer->complete($tr['finishURL']);
		ob_end_clean();

		$msg = '';
		if( $response->status == Auth_OpenID_CANCEL ) {
			// This means the authentication was cancelled.
			$msg = _text("인증이 취소되었습니다.");
		} else if ($response->status == Auth_OpenID_FAILURE) {
			$msg = _text("오픈아이디 인증이 실패하였습니다: ") . $response->message;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			$this->openid = $response->identity_url;
			$this->delegatedid = $response->endpoint->local_id;
			$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
			$this->sreg = $sreg_resp->contents();
			if( !isset($this->sreg['nickname']) ) {
				$this->sreg['nickname'] = "";
			}
			$msg = '';
			if( empty($tr['authenticate_only']) ) {
				$this->setAcl( $this->openid );
				$this->update( $this->openid, $this->delegatedid, $this->sreg['nickname'] );
				if( !empty($tr['need_writers']) ) {
					if( !Acl::check( 'group.writers') ) {
						$msg = _text("관리자 권한이 없는 오픈아이디 입니다") . " : " . $this->openid;
					}
				}
			} else {
				Acl::authorize('openid_temp', $this->openid);
			}
		}

		return $msg ? $this->_redirectWithError( $msg, $tid ) : $this->_redirectWithSucess( $tid );
	}

	function _redirectWithError($msg, $tid)
	{
		$tr = Transaction::unpickle( $tid );
		$requestURI = $tr['requestURI'];
		if( !empty($tr['authenticate_only']) ) {
			$requestURI .= (strchr($requestURI,'?')===false ? "?":"&" ) . "authenticated=0";
		} else {
			$this->setCookie( 'openid_auto', 'n' );
		}
		$this->printErrorReturn( $msg, $requestURI );
	}

	function _redirectWithSucess($tid)
	{
		$tr = Transaction::unpickle( $tid );
		$requestURI = $tr['requestURI'];
		if( !empty($tr['authenticate_only']) ) {
			$requestURI .= (strchr($requestURI,'?')===false ? "?":"&" ) . "authenticated=1";
		} else {
			$this->setCookie( 'openid_auto', 'y' );
		}
		$this->redirect( $requestURI );
	}

	function printErrorReturn( $msg, $location )
	{
		header("HTTP/1.0 200 OK");
		header("Content-type: text/html");
		print "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' /></head><body><script type='text/javascript'>//<![CDATA[" . CRLF . "alert('$msg');document.location.href='$location';//]]>" . CRLF . "</script></body></html>";
		exit(0);
	}

	function redirect( $location )
	{
		header("HTTP/1.0 302 Moved Temporarily");
		header("Location: $location");
		print( "<html><body></body></html>" );
		exit(0);
	}

	function isExisted($openid)
	{
		global $database;
		$blogid = getBlogId();
		$openid = POD::escapeString($openid);

		$query = "SELECT openid FROM {$database['prefix']}OpenIDUsers WHERE blogid={$blogid} and openid='{$openid}'";
		$result = POD::queryCell($query);

		if (is_null($result)) {
			return false;
		}
		return true;
	}

	function setUserInfo( $nickname, $homepage )
	{
		if( !isset( $_SESSION['openid'] ) ) {
			$_SESSION['openid'] = array();
		}
		$_SESSION['openid']['nickname'] = $nickname;
		$_SESSION['openid']['homepage'] = $homepage;
	}

	function logout()
	{
		Acl::authorize('openid', null );
		OpenID::setCookie( 'openid_auto', 'n' );
		OpenIDConsumer::clearUserInfo();
	}

	function clearUserInfo()
	{
		unset( $_SESSION['openid'] );
	}

	function updateUserInfo( $nickname, $homepage )
	{
		global $database;
		$openid = Acl::getIdentity( 'openid' );
		if( empty($openid) ) {
			return false;
		}
		$query = "SELECT data FROM {$database['prefix']}OpenIDUsers WHERE openid='{$openid}'";
		$result = POD::queryCell($query);
		$data = unserialize( $result );

		if( !empty($nickname) ) $data['nickname'] = $nickname;
		if( !empty($homepage) ) $data['homepage'] = $homepage;
		OpenIDConsumer::setUserInfo( $data['nickname'], $data['homepage'] );

		$data = serialize( $data );
		POD::execute("UPDATE {$database['prefix']}OpenIDUsers SET data='{$data}' where openid = '{$openid}'");
	}

	function update($openid,$delegatedid,$nickname,$homepage=null)
	{
		global $database;
		$blogid = getBlogId();
		$openid = POD::escapeString($openid);
		$delegatedid = POD::escapeString($delegatedid);

		$query = "SELECT data FROM {$database['prefix']}OpenIDUsers WHERE openid='{$openid}'";
		$result = POD::queryCell($query);

		if (is_null($result)) {
			$data = serialize( array( 'nickname' => $nickname, 'homepage' => $homepage ) );
			OpenIDConsumer::setUserInfo( $nickname, $homepage );

			/* Owner column is used for reference, all openid records are shared */
			POD::execute("INSERT INTO {$database['prefix']}OpenIDUsers (blogid,openid,delegatedid,firstLogin,lastLogin,loginCount,data) VALUES ($blogid,'{$openid}','{$delegatedid}',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,'{$data}')");
		} else {
			$data = unserialize( $result );

			if( !empty($nickname) ) $data['nickname'] = $nickname;
			if( !empty($homepage) ) $data['homepage'] = $homepage;
			OpenIDConsumer::setUserInfo( $data['nickname'], $data['homepage'] );

			$data = serialize( $data );
			POD::execute("UPDATE {$database['prefix']}OpenIDUsers SET data='{$data}', lastLogin = UNIX_TIMESTAMP(), loginCount = loginCount + 1 where openid = '{$openid}'");
		}
		return;
	}

	function setAcl($openid)
	{
		global $database;

		Acl::authorize('openid', $openid);

		$blogid = getBlogId();
		$query = "SELECT userid FROM {$database['prefix']}UserSettings WHERE name like 'openid.%' and value='{$openid}' order by userid";
		$result = POD::queryRow($query);

		$userid = null;
		if( $result ) {
			$userid = $result['userid'];
			Acl::authorize('textcube', $userid);
		}

		if( !empty($userid) && in_array( "group.writers", Acl::getCurrentPrivilege() ) ) {
			authorizeSession($blogid, $userid);
		} else {
			authorizeSession($blogid, SESSION_OPENID_USERID );
		}
	}

	function setDelegate( $openid )
	{
		if( !Acl::check( array("group.creators") ) ) {
			return false;
		}
		$openid_server = '';
		$xrds_uri = '';
		if( $openid ) {
			list( $openid, $openid_server, $xrds_uri ) = $this->fetchXRDSUri( $openid );
		}
		if( misc::setBlogSettingGlobal( "OpenIDDelegate", $openid ) && 
			misc::setBlogSettingGlobal( "OpenIDServer", $openid_server ) && 
			misc::setBlogSettingGlobal( "OpenIDXRDSUri", $xrds_uri ) ) {
			return true;
		}
		return false;
	}

	function setComment( $mode )
	{
		if( !Acl::check( array("group.administrators") ) ) {
			return false;
		}
		return misc::setBlogSettingGlobal( "AddCommentMode", empty($mode) ? '' : 'openid' );
	}

	function setOpenIDLogoDisplay( $mode )
	{
		if( !Acl::check( array("group.administrators") ) ) {
			return false;
		}
		return misc::setBlogSettingGlobal( "OpenIDLogoDisplay", $mode  );
	}

	function getCommentInfo($blogid,$id){
		global $database;

		$sql="SELECT a.*, openid FROM {$database['prefix']}Comments a LEFT JOIN {$database['prefix']}OpenIDComments b ON a.id = b.id WHERE a.blogid = $blogid AND a.id = $id";
		return POD::queryRow($sql, MYSQL_ASSOC);
	}

	function addComment( $id, $comment )
	{
		/* Assert $id is numeric by the caller function in lib/model/comment.php */

		global $database;
		$blogid = getBlogId();
		$openid = Acl::getIdentity( 'openid' );

		if( $openid )
		{ 
			$result = getCommentAttributes($blogid,$id,"name,homepage");

			POD::execute("UPDATE {$database['prefix']}Comments SET password = '" . OPENID_PASSWORD . "' WHERE blogid = $blogid and id = $id" );
			POD::execute("DELETE FROM {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = $id" );
			POD::execute("INSERT INTO {$database['prefix']}OpenIDComments (blogid,id,openid) values " .
				"( {$blogid}, {$id}, '{$openid}' )");
		}

		if( empty($comment['parent']) )
		{
			return;
		}

		$parent_comment = OpenIDConsumer::getCommentInfo( $blogid, $comment['parent'] );

		/* Check if parent's comment is written by openid and secret. */
		if( ! Acl::check('group.writers') && $parent_comment['openid'] != $openid ) {
			return;
		}

		$result = getCommentAttributes($blogid,$comment['parent'],"secret");
		if( empty($result) || empty($result['secret']) ) {
			return;
		}

		$row = POD::queryRow("SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = {$comment['parent']}" );
		if( empty($row) ) {
			return;
		}
		/* Then, this administor's comment can be secret */
		POD::execute("UPDATE {$database['prefix']}Comments SET secret = 1 WHERE blogid = $blogid and id = $id" );
		return;
	}

	function commentFetchHint( $comment_ids, $blogid )
	{
		global $database;
		global $openid_comments;

		if( empty($openid_comments ) ) {
			$openid_comments = array();
		}
		if( empty($comment_ids ) ) {
			return $comment_ids;
		}

		$query_candidate_ids = array();
		$openid_comments_keys = array_keys( $openid_comments );
		foreach( $comment_ids as $id ) {
			if( in_array( "$blogid-$id", $openid_comments_keys ) ) {
				continue;
			}
			array_push( $query_candidate_ids, $id );
		}

		if( empty( $query_candidate_ids ) ) {
			return $comment_ids;
		}

		$ids = join( ',', $query_candidate_ids );
		$sql = "SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id in ( $ids )";
		$row = POD::queryAll($sql);

		$cached_ids = array();
		if( !empty($row) ) {
			foreach( $row as $openid_cmt ) {
				$openid_comments[ $openid_cmt['blogid']."-".$openid_cmt['id'] ] = $openid_cmt;
				array_push( $cached_ids, $openid_cmt['id'] );
			}
		}
		foreach( $comment_ids as $id ) {
			if( in_array( $id, $cached_ids ) || in_array( "$blogid-$id", $openid_comments_keys ) ) {
				continue;
			}
			$openid_comments[ "$blogid-$id" ] = array();
		}
		return $comment_ids;
	}

	function getOpenIDComment( $blogid, $id )
	{
		global $database;
		global $openid_comments;

		if( isset($openid_comments["$blogid-$id"]) ) {
			return $openid_comments["$blogid-$id"];
		}
		$row = POD::queryRow("SELECT * from {$database['prefix']}OpenIDComments WHERE blogid = $blogid and id = $id" );
		if( empty($row) )
		{
			return null;
		}
		if( empty($openid_comments) ) {
			$openid_comments = array();
		}
		$openid_comments[ "$blogid-$id" ] =& $row;
		return $row;
	}

	function showSecretComment($comment)
	{
		$blogid = getBlogId();

		if( !$comment['secret'] || !Acl::getIdentity('openid') ) {
			return false;
		}

		$row = OpenIDConsumer::getOpenIDComment( $comment['blogid'], $comment['id'] );
		if( !empty($row) && $row['openid'] == Acl::getIdentity( 'openid' ) ) {
			return true;
		}
		if( empty($comment['parent']) ) {
			return false;
		}
		$row = OpenIDConsumer::getOpenIDComment( $comment['blogid'], $comment['parent'] );
		if( empty($row) ) {
			return false;
		}
		if( $row['openid'] == Acl::getIdentity( 'openid' ) ) {
			return true;
		}
		return false;
	}

}