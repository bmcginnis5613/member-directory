# Boardroom Member Directory

A lightweight WordPress plugin that displays a filterable member directory for a specific WooCommerce Membership plan. No bloated theme or community platform required.

---

## Requirements

- WordPress 6.0+
- WooCommerce
- WooCommerce Memberships

---

## Installation

1. Upload the `boardroom-member-directory` folder to `/wp-content/plugins/`
2. Activate the plugin via **Plugins → Installed Plugins**
3. Go to **Settings → Member Directory** and select the membership plan whose members should appear in the directory
4. Create or edit any page/post and add the shortcode:

```
[member_directory]
```

---

## Shortcode Options

| Attribute | Default        | Description                               |
|-----------|----------------|-------------------------------------------|
| `plan`    | *(from settings)* | WooCommerce Membership plan slug       |
| `columns` | `3`            | Cards per row: `2`, `3`, or `4`           |
| `search`  | `true`         | Show live-search bar: `true` or `false`   |

**Examples:**
```
[member_directory]
[member_directory plan="executive-network" columns="4"]
[member_directory search="false"]
```

---

## Member Profile Fields

These custom fields appear on each user's admin profile page (**Users → Edit User**) under the **Member Directory Profile** section:

| Meta Key       | Label                  |
|----------------|------------------------|
| `bmd_title`    | Job Title / Role       |
| `bmd_company`  | Company / Organization |
| `bmd_industry` | Industry / Sector      |
| `bmd_linkedin` | LinkedIn Profile URL   |

Admins can edit these fields for any user in the WordPress back-end. Members can edit their own fields via the optional front-end form shortcode:

```
[member_profile_form]
```

---

## What Shows on a Card

- **Avatar** — Gravatar (falls back to WordPress default)
- **Name** — Links to LinkedIn profile if one is set
- **Job Title / Role**
- **Company / Organization**
- **Industry / Sector**
- **Email address**

---

## Access Control

The directory is only rendered for **logged-in users**. Logged-out visitors see a configurable login prompt. The login notice can be customised via the `bmd_login_notice` filter:

```php
add_filter( 'bmd_login_notice', function( $message ) {
    return 'Members only. <a href="' . wp_login_url() . '">Sign in</a> to view the directory.';
} );
```

---

## Extending

### Adding more custom fields

Edit `includes/class-bmd-fields.php` and add entries to `get_field_definitions()`. The field will automatically appear in:
- The admin user-edit screen
- The front-end profile form
- The directory card (add the appropriate `get_user_meta()` call in `templates/directory.php`)

### Overriding the card template

Copy `templates/directory.php` to your theme at `your-theme/boardroom-member-directory/directory.php`. The plugin checks for a theme override automatically (add this to your main plugin file if desired via `locate_template()`).

---

## File Structure

```
boardroom-member-directory/
├── boardroom-member-directory.php   ← Main plugin file
├── README.md
├── assets/
│   ├── css/
│   │   └── directory.css            ← Front-end styles
│   └── js/
│       └── directory.js             ← Live search
├── includes/
│   ├── class-bmd-fields.php         ← Custom user meta fields
│   ├── class-bmd-query.php          ← WooCommerce Memberships query
│   ├── class-bmd-shortcode.php      ← [member_directory] shortcode
│   ├── class-bmd-admin.php          ← Settings page
│   └── class-bmd-profile.php        ← [member_profile_form] shortcode
└── templates/
    └── directory.php                ← Card grid HTML
```
