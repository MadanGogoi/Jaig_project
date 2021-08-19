<?php
CRUD::resource('parent', 'UserCrudController');
CRUD::resource('feedback', 'FeedbackCrudController');
CRUD::resource('child', 'ChildCrudController');
CRUD::resource('childreport', 'ChildreportCrudController');
CRUD::resource('spacerdata', 'SpacerdataCrudController');
CRUD::resource('faq', 'FaqCrudController');
CRUD::resource('reward', 'RewardCrudController');
CRUD::resource('role', 'RoleCrudController');


Route::get('dashboard', 'AdminCrudController@dashboard');
Route::get('report', 'ChildreportCrudController@report');
Route::get('report/{id}/attack/{type}/{startdate}', 'ChildreportCrudController@attack');
Route::get('report/{id}/technique_compliance/{type}/{startdate}', 'ChildreportCrudController@technique_compliance');
Route::get('export', 'UserCrudController@export')->name('export');
Route::get('export_techcompaliance', 'ChildreportCrudController@export_techcompaliance')->name('export_techcompaliance');
Route::get('export_attack', 'ChildreportCrudController@export_attack')->name('export_attack');

Route::prefix('parent/{user_id}/')->group(function () {
	CRUD::resource('viewchild', 'ParentchildCrudController');
});
Route::prefix('child/{child_id}/')->group(function () {
	CRUD::resource('viewspacerdata', 'ChildspacerdataCrudController');
	CRUD::resource('viewreward', 'ChildrewardCrudController');
	CRUD::resource('viewreport', 'ReportCrudController');
});