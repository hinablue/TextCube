#!/usr/bin/php5
<?php

	/**
	 *  This is a php-plurk-api example.
	 *  @package php-plurk-api
	 *  @desc    Get the API key via Official API website, http://www.plurk.com/API
     *  @see     http://www.plurk.com/API
	 *
	 **/

	$api_key = '';
	$username = '';
	$password = '';

	require('plurk_api.php');

	$plurk = new plurk_api();
	$plurk->login($api_key, $username, $password);

	/**
	 ******************************************
	 * Get plurks
	 *
	 * set plurk id = {123, 456, 789}
	 ******************************************/

	echo "\n\n ----- get plurks ----- \n";
	print_r($plurk->get_plurks(date('c'), 20);

	// echo "\n\n ----- get someone's plurk ----- \n";
	// print_r($plurk->get_plurk(123));

	// echo "\n\n ----- get unread plurks ----- \n";
	// print_r($plurk->get_unread_plurks());

	// echo "\n\n ----- mark plurk as read ----- \n";
	// $plurk->mark_plurk_as_read(array(123,456,789));

	// echo "\n\n ----- add plurk ----- \n";
	// $plurk->add_plurk('en', 'says', 'Hello World');

	// echo "\n\n ----- edit plurk ----- \n";
	// $plurk->edit_plurk(123, 'be edited');

	// echo "\n\n ----- delete plurk ----- \n";
	// $plurk->delete_plurk(123);

	// echo "\n\n ----- mute plurks ----- \n";
	// print_r($plurk->mute_plurks(123));

	// echo "\n\n ----- unmute plurks ----- \n";
	// print_r($plurk->unmute_plurks(123));


	/**
	 ******************************************
	 * Get alerts
	 *
	 ******************************************/

	// echo "\n\n ----- get active alerts ----- \n";
	// print_r($plurk->get_active());

	// echo "\n\n ----- get a list of past 30 alerts ----- \n";
	// print_r($plurk->get_history());

	// echo "\n\n ----- remove notification ----- \n";
	// $plurk->remove_notification(123);


	/**
	 ******************************************
	 * Get plurk's responses
	 *
	 ******************************************/

	// echo "\n\n ----- get responses ----- \n";
	// echo "set plurk id = 123\n";
	// print_r($plurk->get_responses(123));

	// echo "\n\n ----- add response ----- \n";
	// echo "set plurk id = 123\n";
	// print_r($plurk->add_response(123, 'test response', 'says'));

	// echo "\n\n ----- delete response ----- \n";
	// echo "set plurk id = 123, response id = 456\n";
	// $plurk->delete_response(123, 456);


	/**
	 ******************************************
	 * Control user
	 *
	 ******************************************/

	// echo "\n\n ----- get own profile ----- \n";
	// print_r($plurk->get_own_profile());

	// echo "\n\n ----- get user public profile ----- \n";
	// echo "set user id = 123\n";
	// print_r($plurk->get_public_profile(123));

	// echo "\n\n ----- get user info ----- \n";
	// print_r($plurk->get_user_info());

	// echo "\n\n ------ get users friends (nick name and full name)\n";
	// print_r($plurk->get_completion());

	// echo "\n\n ----- get block user's list ----- \n";
	// print_r($plurk->get_blocks());

	// echo "\n\n ----- block user ----- \n";
	// $plurk->block_user(5366984);

	// echo "\n\n ----- unblock user ----- \n";
	// $plurk->unblock_user(5366984);


	/**
	 ******************************************
	 * Control friends
	 *
	 * set user id = 123
	 * set friend id = 789
	 ******************************************/

	// echo "\n\n ----- get someone's friends ----- \n";
	// print_r($plurk->get_friends(123));

	// echo "\n\n ----- become someone's friend ----- \n";
	// $plurk->become_friend(789);

	// echo "\n\n ----- remove friend ----- \n";
	// $plurk->remove_friend(789);

	// echo "\n\n ----- accept friendship request as friend ----- \n";
	// $plurk->add_as_friend(789);

	// echo "\n\n ----- accept all friendship requests as friends ----- \n";
	// $plurk->add_all_as_friends();

	// echo "\n\n ----- deny friendship ----- \n";
	// $plurk->deny_friendship(789);


	/*
	 ******************************************
	 * Control fans
	 *
	 * set user id = 123
	 * set fan id = 789
	 ******************************************/

	// echo "\n\n ----- get following ----- \n";
	// print_r($plurk->get_following());

	// echo "\n\n ----- get someone's fans ----- \n";
	// print_r($plurk->get_fans(123));

	// echo "\n\n ----- become someone's fan ----- \n";
	// $plurk->become_fan(5366983);

	// echo "\n\n ----- accept a friendship request as fan ----- \n";
	// plurk->add_as_fan(789);

	// echo "\n\n ----- accept all friendship requests as fans ----- \n";
	// $plurk->add_all_as_fan();

	/* can't use */
	// echo "\n\n ----- set user following ----- \n";
	// echo "user id = 789\n";
	// echo ($plurk->set_following(3440147, $follow = FALSE)) ? 'success' : 'disable';


	/*
	 ******************************************
	 * Search
	 *
	 ******************************************/

	// echo "\n\n ----- search plurk ----- \n";
	// print_r($plurk->search_plurk('php-plurk-api'));

	// echo "\n\n ----- search user ----- \n";
	// print_r($plurk->search_user('roga lin'));

	// echo "\n\n ----- get emoticons ----- \n";
	// print_r($plurk->get_emoticons());

	/*
	 ******************************************
	 * Clique
	 *
	 ******************************************/

	// echo "\n\n ----- get clique list ----- \n";
	// print_r($plurk->get_cliques());

	// echo "\n\n ----- get clique ----- \n";
	// print_r($plurk->get_clique('test1'));

	// echo "\n\n ----- create a clique ----- \n";
	// print_r($plurk->create_clique("test"));

	// echo "\n\n ----- rename clique ----- \n";
	// print_r($plurk->rename_clique("test","test1"));

	// echo "\n\n ----- add a user to a clique ----- \n";
	// print_r($plurk->add_to_clique("test1", 3440147));

	// echo "\n\n ----- remove a user from a clique ----- \n";
	// print_r($plurk->remove_from_clique("test1", 3440147));

?>