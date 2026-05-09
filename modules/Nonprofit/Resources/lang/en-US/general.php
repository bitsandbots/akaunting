<?php

return [
    'name'          => 'Nonprofit Accounting',
    'description'   => 'Fund-based accounting with double-entry ledger, grants management, and FASB-compliant reporting.',

    // Chart of Accounts
    'coa'           => 'Chart of Accounts|Chart of Accounts',
    'coa_code'      => 'Account Code',
    'coa_name'      => 'Account Name',
    'coa_type'      => 'Account Type',
    'coa_parent'    => 'Parent Account',
    'parent_coa'    => 'Parent Account',
    'account_type'  => 'Account Type',
    'select_type'   => 'Select Account Type',

    // Funds
    'fund'          => 'Fund|Funds',
    'fund_code'     => 'Fund Code',
    'fund_name'     => 'Fund Name',
    'fund_type'     => 'Fund Type',
    'parent_fund'   => 'Parent Fund',
    'restriction'   => 'Restriction Detail',
    'fund_restriction' => 'Donor Restriction',

    // Programs
    'program'       => 'Program|Programs',
    'program_code'  => 'Program Code',
    'program_name'  => 'Program Name',
    'start_date'    => 'Start Date',
    'end_date'      => 'End Date',

    // Functional Classes
    'functional_class'       => 'Functional Class|Functional Classes',
    'functional_class_code'  => 'Class Code',
    'functional_class_name'  => 'Class Name',
    'parent_class'           => 'Parent Classification',
    'is_system'              => 'System Row',

    // Fiscal Periods
    'fiscal_period'          => 'Fiscal Period|Fiscal Periods',
    'fiscal_period_name'     => 'Period Name',
    'period_status'          => 'Status',
    'period_closed_at'       => 'Closed At',
    'period_closed_by'       => 'Closed By',
    'period_overlap_error'   => 'This period\'s date range overlaps with an existing fiscal period.',

    // Journal Entries
    'journal_entry'          => 'Journal Entry|Journal Entries',
    'entry_number'           => 'Entry Number',
    'entry_date'             => 'Entry Date',
    'reference'              => 'Reference',
    'status'                 => 'Status',
    'draft'                  => 'Draft',
    'posted'                 => 'Posted',
    'reversed'               => 'Reversed',
    'void'                   => 'Void',
    'post'                   => 'Post',
    'reverse'                => 'Reverse',
    'void_entry'             => 'Void Entry',
    'post_confirmation'      => 'Are you sure you want to post this journal entry? This action cannot be undone.',
    'reverse_confirmation'   => 'Are you sure you want to reverse this journal entry?',
    'reverse_reason'         => 'Reason for Reversal',
    'void_confirmation'      => 'Are you sure you want to void this draft entry?',
    'posted_at'              => 'Posted At',
    'posted_by'              => 'Posted By',
    'reverses_entry'         => 'Reverses Entry',
    'reversed_by_entry'      => 'Reversed By Entry',
    'lines'                  => 'Lines',
    'add_line'               => 'Add Line',
    'remove_line'            => 'Remove Line',
    'account'                => 'Account',

    // Line fields
    'debit_amount'           => 'Debit',
    'credit_amount'          => 'Credit',
    'debit_foreign'          => 'Debit (Foreign)',
    'credit_foreign'         => 'Credit (Foreign)',
    'currency_code'          => 'Currency',
    'currency_rate'          => 'Exchange Rate',

    // Dimensions
    'dimensions'             => 'Dimensions',
    'fund_id'                => 'Fund',
    'program_id'             => 'Program',
    'functional_class_id'    => 'Functional Class',
    'select_fund'            => 'Select Fund',
    'select_program'         => 'Select Program',
    'select_functional_class' => 'Select Functional Class',

    // Transaction
    'transaction_id'         => 'Source Transaction',

    // Validation
    'cannot_delete_system_row'               => 'System rows cannot be deleted.',
    'cannot_delete_system_functional_class' => 'System functional classes cannot be deleted.',
    'cannot_delete_posted_journal_entry' => 'Posted journal entries cannot be deleted. Reverse them instead.',
    'cannot_modify_posted_journal_entry' => 'Posted journal entries are immutable. Disallowed fields: :fields.',
    'cannot_modify_posted_journal_entry_line' => 'Cannot :verb a journal entry line: parent entry is :status.',
    'entry_balanced'             => 'Debits equal credits.',
    'entry_not_balanced'         => 'Journal entry is not balanced.',
    'min_two_lines'              => 'At least 2 lines are required.',

    // Settings
    'account_mappings'       => 'Account Mappings',
    'account_mappings_description' => 'Map Akaunting income/expense categories to ledger accounts.',
    'dimension_defaults'     => 'Dimension Defaults',
    'dimension_defaults_description' => 'Set default fund, program, and functional class for transactions.',
    'default_fund'           => 'Default Fund',
    'default_program'        => 'Default Program',
    'default_functional_class' => 'Default Functional Class',

    // Messages
    'created'                => ':type created successfully.',
    'updated'                => ':type updated successfully.',
    'deleted'                => ':type deleted successfully.',
    'posted_success'         => 'Journal entry posted successfully.',
    'reversed_success'       => 'Journal entry reversed successfully.',
    'voided_success'         => 'Draft entry voided successfully.',
    'no_open_period'         => 'No open fiscal period covers the entry date.',
    'general_error'          => 'An error occurred. Please try again.',

    // Reports
    'report'                 => 'Report|Reports',

    // Types
    'asset'                  => 'Asset',
    'liability'              => 'Liability',
    'equity'                 => 'Equity',
    'revenue'                => 'Revenue',
    'expense'                => 'Expense',
    'without_donor_restrictions' => 'Without Donor Restrictions',
    'with_donor_restrictions'    => 'With Donor Restrictions',
    'program_services'       => 'Program Services',
    'management_general'     => 'Management & General',
    'fundraising'            => 'Fundraising',
    'open'                   => 'Open',
    'closed'                 => 'Closed',

    // Form
    'basic'                  => 'Basic',
    'save'                   => 'Save',
    'save_draft'             => 'Save as Draft',
    'save_post'              => 'Save & Post',
    'delete'                 => 'Delete',
    'delete_confirm'         => 'Are you sure you want to delete this :type?',
    'cancel'                 => 'Cancel',
    'edit'                   => 'Edit',
    'no_data'                => 'No :type found.',

    // System
    'yes'                    => 'Yes',
    'no'                     => 'No',
    'actions'                => 'Actions',
];
