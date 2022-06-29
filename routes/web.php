<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\Group\GroupController;
use App\Http\Controllers\Group\GroupMemberController;
use App\Http\Controllers\Invitation\GuestInvitationController;
use App\Http\Controllers\Invitation\UserInvitationController;
use App\Http\Controllers\Invitation\WorkspaceInvitationController;
use App\Http\Controllers\Layer\LayerController;
use App\Http\Controllers\Layer\SearchLayerController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectMemberController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Ticket\ChildTicketController;
use App\Http\Controllers\Ticket\SearchTicketController;
use App\Http\Controllers\Ticket\TicketController;
use App\Http\Controllers\Ticket\TicketTableController;
use App\Http\Controllers\TicketType\CopyTicketTypeController;
use App\Http\Controllers\TicketType\SearchTicketTypeController;
use App\Http\Controllers\TicketType\TicketTypeController;
use App\Http\Controllers\TimeEntry\EstimateController;
use App\Http\Controllers\TimeEntry\SpentController;
use App\Http\Controllers\User\SearchUserController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Workspace\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')->group(function () {
    // Auth
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    // Register
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->name('register.store');

    // Invitation
    Route::post('invitation/guest/{invitation:token}', [GuestInvitationController::class, 'update'])
        ->name('invitation.accept.guest');

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

// Invitation
Route::get('invitation/guest/{invitation:token}', [GuestInvitationController::class, 'show'])
    ->name('invitation.show');

Route::middleware(['auth', 'workspace.permission'])->group(function () {

    Route::get('', function (\Illuminate\Http\Request $request) {
        return redirect()->route('dashboard', $request->user()->workspace_id);
    });

    Route::post('upload/post', [MediaController::class, 'store'])->name('media.store');

    Route::prefix('{workspace}')->group(function () {

        // Auth
        Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('people')->group(function () {

            // Users
            Route::get('user/search', [SearchUserController::class, 'index'])->name('user.search');

            Route::put('user/{user}/restore', [UserController::class, 'restore'])->name('user.restore');

            Route::resource('user', UserController::class)->only([
                'index', 'update', 'destroy'
            ]);

            // Roles
            Route::resource('role', RoleController::class)->only([
                'index', 'store', 'update', 'destroy'
            ]);

            // Groups
            Route::get('group/search/{group}', [GroupController::class, 'index'])->name('group.index');

            Route::delete('group/{group}', [GroupController::class, 'destroy'])->name('group.destroy');

            Route::resource('group', GroupController::class)->only([
                'store', 'show', 'update'
            ]);

            // Group members
            Route::post('group/{group}/member/delete', [GroupMemberController::class, 'destroy'])
                ->name('group.member.destroy');

            Route::resource('group.member', GroupMemberController::class)->only([
                'store'
            ]);

            // Clients
            Route::delete('client', [ClientController::class, 'destroy'])
                ->name('client.destroy');

            Route::resource('client', ClientController::class)->only([
                'index', 'store', 'update',
            ]);

        });


        Route::middleware('carbon.interval')->group(function () {

            // Projects
            Route::resource('project', ProjectController::class)->only([
                'index', 'store', 'update', 'destroy', 'show'
            ]);

            // Project members
            Route::resource('project.member', ProjectMemberController::class)->only([
                'store'
            ]);

            Route::delete('project/{project}/member',
                [ProjectMemberController::class, 'destroy'])->name('project.member.destroy');


            Route::prefix('project/{project}')->group(function () {

                // Layers
                Route::get('layer/search', [SearchLayerController::class, 'index'])->name('layer.search');

                Route::resource('layer', LayerController::class)->only([
                    'index', 'store', 'update', 'destroy'
                ]);

                /**
                 * Time Entries
                 */
                Route::resource('ticket.time-spent', SpentController::class)->only([
                    'index', 'store', 'destroy'
                ]);

                Route::resource('ticket.time-estimate', EstimateController::class)->only([
                    'index', 'store'
                ]);

                // Tickets
                Route::middleware('can:view,project')->group(function () {

                    Route::get('ticket/search', [SearchTicketController::class, 'index'])->name('ticket.search');

                    Route::post('ticket/table', [TicketTableController::class, 'index'])->name('ticket.table');

                    Route::resource('ticket', TicketController::class)->only([
                        'index', 'show', 'store', 'update', 'destroy'
                    ]);

                    Route::resource('ticket.child', ChildTicketController::class)->only([
                        'store', 'destroy'
                    ]);

                    Route::resource('ticket.comment', CommentController::class)->only([
                        'store', 'destroy'
                    ]);
                });

            });

        });


        Route::put('workspace', [WorkspaceController::class, 'update'])
            ->name('workspace.update');

        Route::delete('workspace', [WorkspaceController::class, 'destroy'])
            ->name('workspace.destroy');

        Route::post('workspace', [WorkspaceController::class, 'store'])
            ->name('workspace.store');

        Route::prefix('templates')->group(function () {

            // Ticket types
            Route::prefix('ticket-type')->group(function () {

                Route::get('search', [SearchTicketTypeController::class, 'index'])->name('ticket-type.search');

                Route::delete('', [TicketTypeController::class, 'destroy'])->name('ticket-type.destroy');

                Route::delete('{ticketType}/force', [TicketTypeController::class, 'forceDestroy'])
                    ->name('ticket-type.destroy.force');

                Route::put('{ticketType}/restore', [TicketTypeController::class, 'restore'])
                    ->name('ticket-type.restore')
                    ->withTrashed();

                Route::post('{ticketType}/copy', [CopyTicketTypeController::class, 'store'])
                    ->name('ticket-type.copy');
            });

            Route::resource('ticket-type', TicketTypeController::class)->only([
                'index', 'store', 'update'
            ])->parameters(['ticket-type' => 'ticketType']);

        });

        // Invitation
        Route::prefix('invitation')->group(function () {

            // User Invitation
            Route::prefix('user')->group(function () {

                Route::post('', [UserInvitationController::class, 'store'])
                    ->name('invitation.store');

                Route::delete('{token}', [UserInvitationController::class, 'destroy'])
                    ->name('invitation.decline.user');

            });

            ///Workspace invitation
            Route::prefix('workspace')->group(function () {

                Route::post('{token}', [WorkspaceInvitationController::class, 'update'])
                    ->name('invitation.accept.user');

                Route::delete('{token}', [WorkspaceInvitationController::class, 'destroy'])
                    ->name('invitation.decline.workspace');
            });

        });

        //Global search
        Route::get('global-search', [GlobalSearchController::class, 'search'])->name('global.search');
    });

});