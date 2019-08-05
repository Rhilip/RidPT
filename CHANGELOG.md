<a name="unreleased"></a>
## [Unreleased]

### Build
- **Composer:** Update mirror address

### Chore
- **gitignore:** Add ignore of `/backup` folder

### Feat
- **Category:** Add Categories Support when upload torrent
- **Category:** Add Image and class_name support
- **Category:** Add Categories Manage Pane
- **Crontab:** Move From Timer to Process
- **Gravatar:** Add support of gravatar
- **Process:** Add custom Process Support
- **Redis:** Add mutiDelete() function for Redis
- **Tracker:** Move From Timer to Process
- **Tracker:** Add `retry in` field when failed
- **User:** Add Bonus And Unread Messsage count
- **UserInfo:** Add Cache Lock of user access_{time,ip} update
- **ban:** Add table `ban_usernames` and `ban_emails`
- **csrf:** Add Csrf Support
- **email:** Use Site::sendEmail to simple email sender
- **system:** can get more system info via class SystemInfoHelper

### Fix
- **Anonymous:** Fix Auth Page 500 after commit `2cd1a499`
- **Cookies:** Fix session sep from `%` to `_`
- **Database:** Fix table `links` miss
- **Env:** Exit when parse env file failed
- **Form:** Miss use flag since namespace change
- **Form:** Link\EditForm Update diff
- **Requests:** Fix fullUrl() may add unnecessary `?`
- **Tracker:** Fix SQL error
- **Tracker:** Fix TrackerException Logger
- **categories:** Remove key `sort_index`
- **redis_key:** Fix array value cause parser error

### Perf
- **Category:** Remove apps/models/Category
- **Site:** Move apps/{libraries->components}/Site
- **Site:** try to cache re-hit
- **Tracker:** Use brpoplpush to get announce data from redis
- **captcha:** simple captcha input
- **tracker:** No need to explicit serialize announce data

### Refactor
- **Coroutine:** Remove Coroutine Model, Judge part
- **Coroutine:** Remove Coroutine Model
- **Tracker:** Better Tracker behaviour in multi tracker
- **View:** Rename folder `error` to `action`
- **action_success:** Simple The Action Template

### Style
- **Auth:** Sort Auth Form
- **js:** merge separate js file to `main.js`
- **printIn:** Add datetime tag


<a name="v0.1.4-alpha"></a>
## [v0.1.4-alpha] - 2019-06-28
### Chore
- **User:** Make User component as Part of App but not framework

### Docs
- **Licence:** Add Licence Checker By FOSSA
- **Migration:** Update Nginx config
- **README:** Fix Mailer Env typo
- **release:**  v0.1.4-alpha

### Feat
- **Auth:** Add UserRecover Form
- **Auth:** Add full Advanced Options for Login
- **Cleanup:** Add disable cleanup job by set priority to 0
- **Cleanup:** Cleanup dead peers
- **Debug:** Add extend debug output
- **Debug:** Remove DebugMiddleware
- **Email:** Add email template
- **Form Validator:** Add library bootstrap-validator library
- **Frontend:** Add localforage As Cache Provider
- **Invite:** Finish invite system
- **Invite:** Add base invite table
- **Links:** Add full Links manage
- **News:** Fix news tag not appear in index
- **News:** Add Site News model
- **Register:** Can't copy password and paste to retyep_password
- **Response:** Add redirect code
- **Task:** Add task process support
- **Timer:** Add Timer Example
- **Torrent:** Add folder size sum in torrent file modal
- **Typo:** Fix Typo of `secret`
- **framework:** Remove Swoole Task Support

### Fix
- **Admin Panel:** Fix index num in Redis Keys Page
- **Auth:** Fix Broken Auth page after frontend framework change
- **Captcha:** Fix style of captcha
- **DATABASE:** Fix CURRENT_TIMESTAMP Extra error
- **Email:** Fix Email Can't Send
- **Error:** Fix Error Handler
- **Invite:** Add Fine-grained control of recycle pending
- **Links:** Remove extra meta section
- **Register:** Add captcha checker
- **TorrentUpload:** Fix length 0 file cause ParseErrorException
- **Tracker:** Add miss port check for field ipv6_port
- **View:** Fix Conversion::setDefault()
- **View:** remove view helper function `get_torrent_uploader_id`
- **View:** Fix view function redeclare

### Perf
- **BBCode:** Use mjohnson/decoda to parse and cache BBCode
- **Config:** Add quick ref config()
- **Config:** Remove `configTable_construct_lock`
- **DATABASE:** Drop Table `files`
- **Tracker:** Use Task process to quick response Tracker Announce Action
- **Tracker:** Add passkey and info_hash Filter

### Refactor
- **InviteAction:** Use InviteActionFrom instead of func in Controller
- **Mailer:** Use phpmailer to replace swiftmailer
- **Redis:** Add more Redis arguments in debug output
- **Session:** Add Session Format Docs
- **SiteConfig:** change namespace `authority.route_` to `route.`
- **Tracker:** Separate announce data update function
- **Validator:** Add function buildDefaultValue()
- **ext2Icon:** Add more File format
- **timer:** Change Namespace of Timer

### Revert
- **Http:** Remove Request::getUserAgent()

### Style
- **Admin Panel:** Fix Broken View Admin Panel
- **Auth:** Not output extend debug info for anonymous. (In route `/auth`)
- **Frontend:** Back frontend framework to Zui
- **View:** Add top util by layui.util

### Pull Requests
- Merge pull request [#2](https://github.com/Rhilip/ridpt/issues/2) from fossabot/master

### BREAKING CHANGE

Remove Swoole Task Support

DATABASE structure changed

Database Structure of Table `invite`,`user_invitations` Change

Some Table added or name changed

Database Structure of `users_confirm` Change

Database Stucture of `users_confirms` change

DB structure change: TABLE `news` added

dbstructure of `site_crontab` Change


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

[Unreleased]: https://github.com/Rhilip/ridpt/compare/v0.1.4-alpha...HEAD
[v0.1.4-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.3-alpha...v0.1.4-alpha
[v0.1.3-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.2-alpha...v0.1.3-alpha
[v0.1.2-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.1-alpha...v0.1.2-alpha
[v0.1.1-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.0-alpha...v0.1.1-alpha
