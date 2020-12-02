<?php

// Public routes
Route::get('me', "User\MeController@getMe");

//Get designs
Route::get('designs', "Designs\DesignController@index");
Route::get('designs/{id}', "Designs\DesignController@findDesign");

//Get users
Route::get('users', "User\UserController@index");

// Get teams by slug
Route::get('teams/slug/{slug}', 'Teams\TeamsController@findBySlug');

// Search Designs
Route::get('search/designs', 'Designs\DesignController@search');
Route::get('search/designers', 'User\UserController@search');

// Route group for authenticated users only
Route::group(['middleware' => ['auth:api']], function () {
    Route::post('logout', 'Auth\LoginController@logout');
    Route::put('settings/profile', 'User\SettingsController@updateProfile');
    Route::put('settings/password', 'User\SettingsController@updatePassword');

    //Designs
    Route::post('designs', 'Designs\UploadController@upload');
    Route::put('designs/{id}', 'Designs\DesignController@update');
    Route::delete('designs/{id}', 'Designs\DesignController@destroy');

    // Likes and Unlikes
    Route::post('designs/{id}/like', 'Designs\DesignController@like');
    Route::get('designs/{id}/liked', 'Designs\DesignController@checkIfUserHasLiked');

    // Comments
    Route::post('designs/{id}/comments', 'Designs\CommentController@store');
    Route::put('comments/{id}', 'Designs\CommentController@update');
    Route::delete('comments/{id}', 'Designs\CommentController@destroy');

    // Teams
    Route::post('teams', 'Teams\TeamsController@store');
    Route::get('teams', 'Teams\TeamsController@index');
    Route::get('teams/{id}', 'Teams\TeamsController@findById');
    Route::get('users/teams', 'Teams\TeamsController@fetchUserTeams');
    Route::put('teams/{id}', 'Teams\TeamsController@update');
    Route::delete('teams/{id}', 'Teams\TeamsController@destroy');
    Route::delete('teams/{team_id}/users/{user_id}', 'Teams\TeamsController@removeFromTeam');

    // Invitations
    Route::post('invitations/{teamId}', 'Invitations\InvitationsController@invite');
    Route::post('invitations/{id}/resend', 'Invitations\InvitationsController@resend');
    Route::post('invitations/{id}/response', 'Invitations\InvitationsController@response');
    Route::delete('invitations/{id}', 'Invitations\InvitationsController@destroy');

    // Chats
    Route::post('chats', 'Chats\ChatsController@sendMessages');
    Route::get('chats', 'Chats\ChatsController@getUserChats');
    Route::get('chats/{id}/messages', 'Chats\ChatsController@getChatMessages');
    Route::put('chats/{id}/markAsRead', 'Chats\ChatsController@markAsRead');
    Route::delete('chats/{id}', 'Chats\ChatsController@destroyMessage');
});

// Route for guests only
Route::group(['middleware' => ['guest:api']], function () {
    Route::post('register', 'Auth\RegisterController@register');
    Route::post('verification/verify/{user}', 'Auth\VerificationController@verfiy')->name('verification.verify');
    Route::post('verification/resend', 'Auth\VerificationController@resend');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.reset');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});
