<a name="unreleased"></a>
## [Unreleased]

### Chore
- **User:** Make User component as Part of App but not framework

### Docs
- **Migration:** Update Nginx config

### Feat
- **Auth:** Add full Advanced Options for Login
- **Auth:** Add UserRecover Form
- **Cleanup:** Cleanup dead peers
- **Cleanup:** Add disable cleanup job by set priority to 0
- **Debug:** Remove DebugMiddleware
- **Debug:** Add extend debug output
- **Invite:** Add base invite table
- **News:** Fix news tag not appear in index
- **News:** Add Site News model
- **Task:** Add task process support
- **Timer:** Add Timer Example
- **Torrent:** Add folder size sum in torrent file modal

### Fix
- **Admin Panel:** Fix index num in Redis Keys Page
- **Auth:** Fix Broken Auth page after frontend framework change
- **Error:** Fix Error Handler
- **Register:** Add captcha checker
- **Tracker:** Add miss port check for field ipv6_port
- **View:** remove view helper function `get_torrent_uploader_id`
- **View:** Fix view function redeclare

### Perf
- **BBCode:** Use mjohnson/decoda to parse and cache BBCode
- **Config:** Remove `configTable_construct_lock`
- **Tracker:** Use Task process to quick response Tracker Announce Action
- **Tracker:** Add passkey and info_hash Filter

### Refactor
- **Redis:** Add more Redis arguments in debug output
- **Session:** Add Session Format Docs

### Revert
- **Http:** Remove Request::getUserAgent()

### Style
- **Admin Panel:** Fix Broken View Admin Panel
- **Auth:** Not output extend debug info for anonymous. (In route `/auth`)
- **Frontend:** Back frontend framework to Zui
- **View:** Add top util by layui.util


<a name="v0.1.3-alpha"></a>
## [v0.1.3-alpha] - 2019-03-16
### Build
- **Compatible:** Remove Compatible Model
- **Environment:** Upgrade Dependency of Mysql and PHP

### Chore
- **Git:** Fix gitkeep file lost
- **Server:** Change the server printing
- **Static File:** Move fonts to public path

### Docs
- **Debug:** End Error Handle Debug
- **Demo:** Change demo site link
- **Readme:** Add Demo Site Information
- **Readme:** Update Readme.md
- **Release:** Release v0.1.3-alpha

### Feat
- **Favour:** Add full favour support
- **Framework:** Add Record of execute sql and redis key hit
- **Front:** Add lib `notice.js`
- **Helper:** Add Simple String crypt helper
- **RSS:** Start Build rss feed
- **Torrent:** Save Torrent File Structure in Table `torrents`
- **Torrent:** Clean temp upload torrent file after success upload
- **Torrent:** Add Torrent FileList View
- **Torrent:** Finish Torrent's Structure part
- **Torrent:** Add Torrent Content Cache
- **Torrent Upload:** Add utf-8 path support for torrent upload
- **UBB:** Add ubb converter
- **User:** Add User Active Seed/Leech Count
- **User:** Add User Confirm Support
- **User:** Add user load by passkey
- **View:** Add ram use status in footer
- **i18n:** Add i18n Support

### Fix
- **Auth:** Fix layout fo Auth Point
- **Auth:** Fix 'errors/action_fail' view should not be touch in AuthController
- **Auth:** Update description texts in `/auth/login`
- **Bencode:** Add full parse check
- **Compatible:** Fix Compatible Model not work
- **Database:** Change Foreign Key behaviour
- **Nameplace:** Fix error nameplace of compatible
- **Namespace:** Namespace miss change
- **Torrent:** Fix Torrent Download Dict miss
- **Upload:** Fix Upload Torrent can't save in database
- **UserTrackerStatus:** Fix redis cache key miss
- **View:** Fix View Don't change after service reload
- **View:** Fix `admin/redis_key` of wrong array echo
- **View:** Fix cost time miss in render

### Perf
- **Admin:** Call redis key info only when pattern give
- **Bencode:** Remove Bencode Library `sandfoxme/bencode`
- **Config:** Create \Swoole\Table as Dynamic Config Provider in Master Process
- **Error:** Fix Error Page
- **Mailer:** Make Mailer Provider extends BaseObject but not Component
- **Middleware:** Fix Middleware behaviour
- **Psr:** Use Psr\Log to simple Rid\Log Component
- **Session:** Not resend set-cookies header for session
- **User:** Quickly get user peer status
- **Validator:** load base rules from call parent method
- **i18n:** Quick load

### Refactor
- **DynamicConfig:** Remove DynamicConfig provider by Redis
- **Framework:** Resort Component folder
- **Framework:** Rename Mix to Rid
- **Framework:** Refactor framework from upstream
- **Helpers:** Remove JsonHelper
- **Output:** Remove \Rid\Console\Output
- **Route:** Fix Route of Torrent
- **Validator:** Change Validator Provider
- **View:** Change template system
- **XML:** Remove XML support

### Revert
- **CSS_TAG:** re-add debug for css_tag
- **Config:** Add back Dynamic Config provider by Redis
- **UploadFile:** Add back some attributes of UploadFile

### Style
- **CSS:** Add css tag
- **Git:** Fix git style in private path
- **Validator:** Separate File and Captcha Validate to Trait object
- **View:** Change View Layout

### BREAKING CHANGE

New db structure `bookmarks`

dbstructure of `Users` Change

Change Validator Provider

frontend framework and backend driver change

Remove Compatible Model

Mysql Version changes from 5.7 to 8.0


<a name="v0.1.2-alpha"></a>
## [v0.1.2-alpha] - 2019-02-01
### Docs
- **Release:** Release v0.1.2-alpha
- **changelog:** Use `git-chglog` to generate CHANGELOG.md

### Feat
- **Login:** Add Support for max login ip test to avoid floor attack
- **User:** User can manager their own sessions

### Fix
- **Auth:** Fix may redirect twice in `/auth/login`
- **Auth:** Fix Login redirect too much

### Refactor
- **Table:** use `agent` to replace `browser` and `platform`

### Style
- **Admin Panel:** replace _ to backspace in render

### BREAKING CHANGE

Structure of Table `users_session_log` Change


<a name="v0.1.1-alpha"></a>
## [v0.1.1-alpha] - 2019-01-31

<a name="v0.1.0-alpha"></a>
## v0.1.0-alpha - 2019-01-30

[Unreleased]: https://github.com/Rhilip/ridpt/compare/v0.1.3-alpha...HEAD
[v0.1.3-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.2-alpha...v0.1.3-alpha
[v0.1.2-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.1-alpha...v0.1.2-alpha
[v0.1.1-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.0-alpha...v0.1.1-alpha
