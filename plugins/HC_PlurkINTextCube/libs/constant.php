<?php

    /**
     *  constants for url setting
     *  @package php-plurk-api
     *  @see     http://www.plurk.com/API
     *
     */

	/**
	 *  Users
	 *  /API/Users/register
	 *  /API/Users/login
	 *  /API/Users/logout
	 *  /API/Users/update
	 *  /API/Users/updatePicture
	 *
	 */

    define('PLURK_REGISTER', 'http://www.plurk.com/API/Users/register');
    define('PLURK_LOGIN', 'http://www.plurk.com/API/Users/login');
    define('PLURK_LOGOUT', 'http://www.plurk.com/API/Users/logout');
    define('PLURK_UPDATE', 'http://www.plurk.com/API/Users/update');
    define('PLURK_UPDATE_PICTURE', 'http://www.plurk.com/API/Users/updatePicture');

	/**
	 *  Real time notifications
	 *  /API/Realtime/getUserChannel
	 *
	 */

	define('PLURK_REALTIME_GET_USER_CHANNEL', 'http://www.plurk.com/API/Realtime/getUserChannel');

	/**
	 *  Polling
	 *  /API/Polling/getPlurks
	 *  /API/Polling/getUnreadCount
	 *
	 */

    define('PLURK_POLLING_GET_PLURK', 'http://www.plurk.com/API/Polling/getPlurks');
    define('PLURK_POLLING_GET_UNREAD_COUNT', 'http://www.plurk.com/API/Polling/getUnreadCount');

    /**
     *  Timeline
     *  /API/Timeline/getPlurk
     *  /API/Timeline/getPlurks
     *  /API/Timeline/getUnreadPlurks
     *  /API/Timeline/plurkAdd
     *  /API/Timeline/plurkDelete
     *  /API/Timeline/plurkEdit
     *  /API/Timeline/mutePlurks
     *  /API/Timeline/unmutePlurks
     *  /API/Timeline/markAsRead
     *  /API/Timeline/uploadPicture
     *
     */

    define('PLURK_TIMELINE_GET_PLURK', 'http://www.plurk.com/API/Timeline/getPlurk');
    define('PLURK_TIMELINE_GET_PLURKS', 'http://www.plurk.com/API/Timeline/getPlurks');
    define('PLURK_TIMELINE_GET_UNREAD_PLURKS', 'http://www.plurk.com/API/Timeline/getUnreadPlurks');
    define('PLURK_TIMELINE_PLURK_ADD', 'http://www.plurk.com/API/Timeline/plurkAdd');
    define('PLURK_TIMELINE_PLURK_DELETE', 'http://www.plurk.com/API/Timeline/plurkDelete');
    define('PLURK_TIMELINE_PLURK_EDIT', 'http://www.plurk.com/API/Timeline/plurkEdit');
    define('PLURK_TIMELINE_MUTE_PLURKS', 'http://www.plurk.com/API/Timeline/mutePlurks');
    define('PLURK_TIMELINE_UNMUTE_PLURKS', 'http://www.plurk.com/API/Timeline/unmutePlurks');
    define('PLURK_TIMELINE_MARK_AS_READ', 'http://www.plurk.com/API/Timeline/markAsRead');
    define('PLURK_TIMELINE_UPLOAD_PICTURE', 'http://www.plurk.com/API/Timeline/uploadPicture');

    /**
     *  Responses
     *  /API/Responses/get
     *  /API/Responses/responseAdd
     *  /API/Responses/responseDelete
     *
     */

    define('PLURK_GET_RESPONSE','http://www.plurk.com/API/Responses/get');
    define('PLURK_ADD_RESPONSE','http://www.plurk.com/API/Responses/responseAdd');
    define('PLURK_DELERE_RESPONSE','http://www.plurk.com/API/Responses/responseDelete');

    /**
     *  Profile
     *  /API/Profile/getOwnProfile
     *  /API/Profile/getPublicProfile
     *
     */

    define('PLURK_GET_OWN_PROFILE','http://www.plurk.com/API/Profile/getOwnProfile');
    define('PLURK_GET_PUBLIC_PROFILE','http://www.plurk.com/API/Profile/getPublicProfile');

    /**
     *  Friends and fans
     *  /API/FriendsFans/getFriendsByOffset
     *  /API/FriendsFans/getFansByOffset
     *  /API/FriendsFans/getFollowingByOffset
     *  /API/FriendsFans/becomeFriend
     *  /API/FriendsFans/removeAsFriend
     *  /API/FriendsFans/becomeFan
     *  /API/FriendsFans/setFollowing
     *  /API/FriendsFans/getCompletion
     *
     */

    define('PLURK_GET_FRIENDS','http://www.plurk.com/API/FriendsFans/getFriendsByOffset');
    define('PLURK_GET_FANS','http://www.plurk.com/API/FriendsFans/getFansByOffset');
    define('PLURK_GET_FOLLOWING','http://www.plurk.com/API/FriendsFans/getFollowingByOffset');
    define('PLURK_BECOME_FRIEND','http://www.plurk.com/API/FriendsFans/becomeFriend');
    define('PLURK_REMOVE_FRIEND','http://www.plurk.com/API/FriendsFans/removeAsFriend');
    define('PLURK_BECOME_FAN','http://www.plurk.com/API/FriendsFans/becomeFan');
    define('PLURK_SET_FOLLOWING','http://www.plurk.com/API/FriendsFans/setFollowing');
    define('PLURK_GET_COMPLETION','http://www.plurk.com/API/FriendsFans/getCompletion');

	/**
	 *  Alerts
	 *  General data structures
	 *  /API/Alerts/getActive
	 *  /API/Alerts/getHistory
	 *  /API/Alerts/addAsFan
	 *  /API/Alerts/addAllAsFan
	 *  /API/Alerts/addAllAsFriends
	 *  /API/Alerts/addAsFriend
	 *  /API/Alerts/denyFriendship
	 *  /API/Alerts/removeNotification
	 *
	 */

    define('PLURK_GET_ACTIVE','http://www.plurk.com/API/Alerts/getActive');
    define('PLURK_GET_HISTORY','http://www.plurk.com/API/Alerts/getHistory');
    define('PLURK_ADD_AS_FAN','http://www.plurk.com/API/Alerts/addAsFan');
    define('PLURK_ADD_AS_FRIEND','http://www.plurk.com/API/Alerts/addAsFriend');
    define('PLURK_ADD_ALL_AS_FAN','http://www.plurk.com/API/Alerts/addAllAsFan');
    define('PLURK_ADD_ALL_AS_FRIEND','http://www.plurk.com/API/Alerts/addAllAsFriends');
    define('PLURK_DENY_FRIEND','http://www.plurk.com/API/Alerts/denyFriendship');
    define('PLURK_REMOVE_NOTIFY','http://www.plurk.com/API/Alerts/removeNotification');

    /**
     * Search
     * /API/PlurkSearch/search
     * /API/UserSearch/search
     *
     */

    define('PLURK_SEARCH','http://www.plurk.com/API/PlurkSearch/search');
    define('PLURK_USER_SEARCH','http://www.plurk.com/API/UserSearch/search');

    /**
     *  Emoticons
     *  /API/Emoticons/get
     *
     */

    define('PLURK_GET_EMOTIONS','http://www.plurk.com/API/Emoticons/get');

    /**
     *  Blocks
     *  /API/Blocks/get
     *  /API/Blocks/block
     *  /API/Blocks/unblock
     *
     */

    define('PLURK_GET_BLOCKS','http://www.plurk.com/API/Blocks/get');
    define('PLURK_BLOCK','http://www.plurk.com/API/Blocks/block');
    define('PLURK_UNBLOCK','http://www.plurk.com/API/Blocks/unblock');

    /**
     *  Cliques
     *  /API/Cliques/getCliques
     *  /API/Cliques/getClique
     *  /API/Cliques/createClique
     *  /API/Cliques/renameClique
     *  /API/Cliques/add
     *  /API/Cliques/remove
     *
     */

    define('PLURK_GET_CLIQUES','http://www.plurk.com/API/Cliques/getCliques');
    define('PLURK_GET_CLIQUE','http://www.plurk.com/API/Cliques/getClique');
    define('PLURK_CREATE_CLIQUE','http://www.plurk.com/API/Cliques/createClique');
    define('PLURK_RENAME_CLIQUE', 'http://www.plurk.com/API/Cliques/renameClique');
    define('PLURK_ADD_TO_CLIQUE', 'http://www.plurk.com/API/Cliques/add');
    define('PLURK_REMOVE_FROM_CLIQUE', 'http://www.plurk.com/API/Cliques/remove');
?>