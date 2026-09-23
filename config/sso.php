<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Microsoft Entra ID single sign-on (admin panel)
    |--------------------------------------------------------------------------
    |
    | On (production): the admin login page offers "Sign in with Microsoft"
    | only, and the email + password form is switched off so it can't be
    | used as a bypass. Off (local, Docker, maintenance): the other way round.
    |
    | Either way a sign-in only succeeds for an existing, active account that
    | holds a panel role — Microsoft proves who someone is, the users table
    | still decides whether they get in. Nothing below is read while SSO is
    | off, so a local checkout boots without any AZURE_* values.
    |
    */

    'enabled' => (bool) env('SSO_ENABLED', false),

    'client_id' => env('AZURE_CLIENT_ID'),

    'client_secret' => env('AZURE_CLIENT_SECRET'),

    // The UniKL tenant's GUID. "common" / "organizations" are refused: they
    // would let accounts from any Microsoft tenant reach the callback.
    'tenant_id' => env('AZURE_TENANT_ID'),

    // Must match the Redirect URI on the Entra app registration exactly.
    // Falls back to this app's own callback route when unset.
    'redirect_uri' => env('AZURE_REDIRECT_URI'),

    'authority' => 'https://login.microsoftonline.com',

    'scopes' => env('AZURE_OAUTH_SCOPES', 'openid profile email offline_access User.Read'),

    'graph_api_url' => env('AZURE_GRAPH_API_URL', 'https://graph.microsoft.com/v1.0'),

    'graph_me_path' => env('AZURE_GRAPH_ME_PATH', '/me'),

    'graph_me_select' => env('AZURE_GRAPH_ME_SELECT', 'id,displayName,mail,userPrincipalName,jobTitle,officeLocation'),

    // The Graph user property holding the UniKL staff ID. Manage Staff adds
    // accounts by email alone, under a placeholder ID; the first Microsoft
    // sign-in swaps in this value. A dotted path reaches into an object,
    // e.g. onPremisesExtensionAttributes.extensionAttribute1.
    'staff_id_attribute' => env('AZURE_STAFF_ID_ATTRIBUTE', 'employeeId'),

    // Exact domain match on the part after "@". A subdomain is a different
    // domain: unikl.edu.my does not cover s.unikl.edu.my (student accounts).
    'allowed_email_domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', strtolower((string) env('SSO_ALLOWED_EMAIL_DOMAINS', 'unikl.edu.my')))
    ))),

];
