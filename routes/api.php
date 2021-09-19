<?php
use Illuminate\Support\Facades\Route;


/************** Authencation Api ********************/
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('mobilelogin', 'AuthController@loginMobile');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::get('me', 'AuthController@me');
    Route::post('verifyemail', 'AuthController@verifyLogin');

});

/************** System menu Api ********************/
Route::get('/menus', 'SystemMenuController@getRoleMenu');
Route::get('/menudashboard', 'SystemMenuController@getMenuTable');
Route::get('/parentmenu', 'SystemMenuController@getParentMenu');
Route::put('/updatemenu', 'SystemMenuController@editMenu');
Route::post('/addmenu', 'SystemMenuController@addMenu');
Route::delete('/deletemenu', 'SystemMenuController@xoaMenu');

/************** Role Api ********************/
Route::get('/roles', 'RoleController@getRoles');
Route::get('/rolemenulist', 'RoleController@getMenuRole');
Route::post('/updaterolemenu', 'RoleController@updateRoleMenu');

/************** User Api ********************/
Route::get('/users', 'UserController@getUsers');
Route::post('/updateuser', 'UserController@updateUser');
Route::post('/adduser', 'UserController@createUser');
Route::post('/activeuser', 'UserController@activeDeactive');
Route::post('/changepassword', 'UserController@changePassword');
Route::post('/uploadavatarprofile', 'UserController@uploadAvatarProfile');
Route::post('/uploadavatar', 'UserController@uploadAvatarManagement');
Route::post('/logoutall', 'UserController@logOutAll');
Route::post('updatemyuser', 'UserController@updateMyUser');

/************** Danh muc Api ********************/
Route::get('/danhmuc', 'DanhMucController@getDanhMuc');
Route::get('/danhmuccon', 'DanhMucController@getDanhMucCon');
Route::post('/danhmuc', 'DanhMucController@addDanhMucCon');
Route::put('/danhmuc', 'DanhMucController@editDanhMucCon');

/************** Cong ty Api ********************/
Route::get('/phongban', 'PhongBanController@getPhongBan');
Route::post('/phongban', 'PhongBanController@addPhongBan');
Route::put('/phongban', 'PhongBanController@editPhongBan');
Route::put('/activephongban', 'PhongBanController@activeDeactive');

Route::get('/nhomto', 'NhomToController@getNhomTo');
Route::get('/nhomtotructhuoc', 'NhomToController@getNhomToTrucThuoc');
Route::post('/nhomto', 'NhomToController@addNhomTo');
Route::put('/nhomto', 'NhomToController@editNhomTo');

/************** Nhan su Api ********************/
Route::get('/nhanvien', 'NhanVienController@getNhanVien');
Route::post('/nhanvien', 'NhanVienController@addNhanVien');
Route::put('/nhanvien', 'NhanVienController@editNhanVien');
Route::get('/showNhanVien', 'NhanVienController@showNhanVien');


/************** Upload File Api ********************/
Route::post('/uploadfile', 'MessageController@uploadFile');


