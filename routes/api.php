<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('register', 'UserController@register');
Route::post('loginApp', 'UserController@login');
Route::post('verifyEmail', 'UserController@verifyEmail');
Route::post('resendVerifyEmail', 'UserController@resendVerifyEmail');
Route::post('logout', 'UserController@logout')->middleware('jwt.verify');


Route::post('postings', 'PostingController@postPostings');
Route::patch('postings/{id}', 'PostingController@putPostings');
Route::get('postings', 'PostingController@getPostings');
Route::get('postings/{id}', 'PostingController@getPostingById');
Route::delete('postings/{id}', 'PostingController@deletePosting');

Route::get('recent', 'PostingController@getRecents');
Route::get('tag', 'PostingController@getTags');
Route::get('categories', 'PostingController@getCategories');

Route::get('portfolio', 'PortfolioController@getPortfolio');
Route::post('portfolio', 'PortfolioController@postPortfolio');
Route::patch('portfolio/{id}', 'PortfolioController@putPortfolio');
Route::delete('portfolio/{id}', 'PortfolioController@deletePortfolio');

// Route::get('categories', 'CategoriesController@getCategories');
// Route::post('categories', 'CategoriesController@postCategories');

//Route::get('booksfree', 'BookController@getFreeBook');
// Route::post('books/', 'BookController@postBook');
Route::post('books/', 'BookController@postBook')->middleware('jwt.verify');
Route::get('books', 'BookController@getBooks')->middleware('jwt.verify');
Route::get('books/{id}', 'BookController@getBookById')->middleware('jwt.verify');
Route::patch('books/{id}', 'BookController@putBookById')->middleware('jwt.verify');
Route::delete('books/{id}', 'BookController@deleteBookById')->middleware('jwt.verify');
Route::delete('books', 'BookController@deleteBooks')->middleware('jwt.verify');

Route::get('user', 'UserController@getAuthenticatedUser')->middleware('jwt.verify');
