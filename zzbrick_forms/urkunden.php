<?php 

// Zugzwang Project
// deutsche-schachjugend.de
// Copyright (c) 2016, 2020 Gustaf Mossakowski <gustaf@koenige.org>
// Formulare: Urkunden


$zz = zzform_include_table('urkunden');

if (!brick_access_rights('Webmaster')) {
	$zz['access'] = 'none';
}
