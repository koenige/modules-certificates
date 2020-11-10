<?php 

// Zugzwang Project
// deutsche-schachjugend.de
// Copyright (c) 2016 Gustaf Mossakowski <gustaf@koenige.org>
// Formulare: Urkunden


require_once $zz_conf['form_scripts'].'/urkunden.php';

if (!brick_access_rights('Webmaster')) {
	$zz['access'] = 'none';
}
