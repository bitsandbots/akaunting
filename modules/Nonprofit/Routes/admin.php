<?php

use Illuminate\Support\Facades\Route;

/**
 * 'admin' middleware and 'nonprofit' prefix applied to all routes (including names)
 *
 * @see \App\Providers\Route::register
 */

Route::admin('nonprofit', function () {
    // Chart of Accounts
    Route::group(['prefix' => 'coa', 'as' => 'coa.'], function () {
        Route::get('/', 'Coa@index')->name('index');
        Route::get('create', 'Coa@create')->name('create');
        Route::post('/', 'Coa@store')->name('store');
        Route::get('{coa}/edit', 'Coa@edit')->name('edit');
        Route::put('{coa}', 'Coa@update')->name('update');
        Route::delete('{coa}', 'Coa@destroy')->name('destroy');
    });

    // Funds
    Route::group(['prefix' => 'funds', 'as' => 'funds.'], function () {
        Route::get('/', 'Funds@index')->name('index');
        Route::get('create', 'Funds@create')->name('create');
        Route::post('/', 'Funds@store')->name('store');
        Route::get('{fund}/edit', 'Funds@edit')->name('edit');
        Route::put('{fund}', 'Funds@update')->name('update');
        Route::delete('{fund}', 'Funds@destroy')->name('destroy');
    });

    // Programs
    Route::group(['prefix' => 'programs', 'as' => 'programs.'], function () {
        Route::get('/', 'Programs@index')->name('index');
        Route::get('create', 'Programs@create')->name('create');
        Route::post('/', 'Programs@store')->name('store');
        Route::get('{program}/edit', 'Programs@edit')->name('edit');
        Route::put('{program}', 'Programs@update')->name('update');
        Route::delete('{program}', 'Programs@destroy')->name('destroy');
    });

    // Functional Classes (Functional Expense Classifications)
    Route::group(['prefix' => 'functional-classes', 'as' => 'functional-classes.'], function () {
        Route::get('/', 'FunctionalClasses@index')->name('index');
        Route::get('create', 'FunctionalClasses@create')->name('create');
        Route::post('/', 'FunctionalClasses@store')->name('store');
        Route::get('{functionalClass}/edit', 'FunctionalClasses@edit')->name('edit');
        Route::put('{functionalClass}', 'FunctionalClasses@update')->name('update');
        Route::delete('{functionalClass}', 'FunctionalClasses@destroy')->name('destroy');
    });

    // Fiscal Periods
    Route::group(['prefix' => 'fiscal-periods', 'as' => 'fiscal-periods.'], function () {
        Route::get('/', 'FiscalPeriods@index')->name('index');
        Route::get('create', 'FiscalPeriods@create')->name('create');
        Route::post('/', 'FiscalPeriods@store')->name('store');
        Route::get('{fiscalPeriod}/edit', 'FiscalPeriods@edit')->name('edit');
        Route::put('{fiscalPeriod}', 'FiscalPeriods@update')->name('update');
        Route::delete('{fiscalPeriod}', 'FiscalPeriods@destroy')->name('destroy');
    });

    // Journal Entries
    Route::group(['prefix' => 'journal-entries', 'as' => 'journal-entries.'], function () {
        Route::get('/', 'JournalEntries@index')->name('index');
        Route::get('create', 'JournalEntries@create')->name('create');
        Route::post('/', 'JournalEntries@store')->name('store');
        Route::get('{journalEntry}', 'JournalEntries@show')->name('show');
        Route::get('{journalEntry}/edit', 'JournalEntries@edit')->name('edit');
        Route::put('{journalEntry}', 'JournalEntries@update')->name('update');
        Route::delete('{journalEntry}', 'JournalEntries@destroy')->name('destroy');
        Route::post('{journalEntry}/post', 'JournalEntries@post')->name('post');
        Route::post('{journalEntry}/reverse', 'JournalEntries@reverse')->name('reverse');
        Route::post('{journalEntry}/void', 'JournalEntries@void')->name('void');
    });

    // Settings
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        // Account Mappings
        Route::group(['prefix' => 'account-mappings', 'as' => 'account-mappings.'], function () {
            Route::get('/', 'Settings\AccountMappings@edit')->name('edit');
            Route::post('/', 'Settings\AccountMappings@update')->name('update');
        });

        // Dimension Defaults
        Route::group(['prefix' => 'dimension-defaults', 'as' => 'dimension-defaults.'], function () {
            Route::get('/', 'Settings\DimensionDefaults@edit')->name('edit');
            Route::post('/', 'Settings\DimensionDefaults@update')->name('update');
        });
    });
});
