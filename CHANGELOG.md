<a name="unreleased"></a>
## [Unreleased]

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

### Feat
- **Favour:** Add full favour support
- **Framework:** Add Record of execute sql and redis key hit
- **Front:** Add lib `notice.js`
- **Helper:** Add Simple String crypt helper
- **RSS:** Start Build rss feed
- **Torrent:** Save Torrent File Structure in Table `torrents`
- **Torrent:** Clean temp upload torrent file after success upload
- **Torrent:** Add Torrent Content Cache
- **Torrent:** Add Torrent FileList View
- **Torrent Upload:** Add utf-8 path support for torrent upload
- **UBB:** Add ubb converter
- **User:** Add User Confirm Support
- **User:** Add user load by passkey
- **User:** Add User Active Seed/Leech Count
- **View:** Add ram use status in footer
- **i18n:** Add i18n Support

### Fix
- **Auth:** Update description texts in `/auth/login`
- **Auth:** Fix 'errors/action_fail' view should not be touch in AuthController
- **Auth:** Fix layout fo Auth Point
- **Bencode:** Add full parse check
- **Compatible:** Fix Compatible Model not work
- **Database:** Change Foreign Key behaviour
- **Nameplace:** Fix error nameplace of compatible
- **Namespace:** Namespace miss change
- **Upload:** Fix Upload Torrent can't save in database
- **UserTrackerStatus:** Fix redis cache key miss
- **View:** Fix cost time miss in render
- **View:** Fix View Don't change after service reload
- **View:** Fix `admin/redis_key` of wrong array echo

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

[Unreleased]: https://github.com/Rhilip/ridpt/compare/v0.1.2-alpha...HEAD
[v0.1.2-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.1-alpha...v0.1.2-alpha
[v0.1.1-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.0-alpha...v0.1.1-alpha
