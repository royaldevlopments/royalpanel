<?php

use Illuminate\Support\Facades\Route;
use RoyalPanel\Http\Controllers\Api\Client\Bot\BotController;

Route::post('/link/generate', [BotController::class, 'generateCode']);
Route::post('/link/verify', [BotController::class, 'verifyLink']);
Route::get('/link/status', [BotController::class, 'linkStatus']);
Route::post('/link/unlink', [BotController::class, 'unlink']);

Route::get('/servers', [BotController::class, 'listServers']);
Route::get('/servers/{id}', [BotController::class, 'serverInfo']);
Route::post('/servers/{id}/power', [BotController::class, 'serverPower']);
Route::post('/servers/{id}/command', [BotController::class, 'serverCommand']);
Route::post('/servers/{id}/action', [BotController::class, 'serverAction']);
Route::patch('/servers/{id}/limits', [BotController::class, 'updateServerLimits']);

Route::get('/users', [BotController::class, 'listUsers']);
Route::post('/users', [BotController::class, 'createUser']);
Route::patch('/users/{id}', [BotController::class, 'updateUser']);
Route::delete('/users/{id}', [BotController::class, 'deleteUser']);
Route::post('/users/{id}/action', [BotController::class, 'userAction']);

Route::get('/nodes', [BotController::class, 'listNodes']);
Route::get('/nests', [BotController::class, 'listNests']);
Route::get('/eggs', [BotController::class, 'listEggs']);
Route::get('/locations', [BotController::class, 'listLocations']);
Route::get('/allocations', [BotController::class, 'listAllocations']);
Route::get('/database-hosts', [BotController::class, 'listDatabaseHosts']);

Route::get('/stats', [BotController::class, 'stats']);
Route::get('/config', [BotController::class, 'config']);
