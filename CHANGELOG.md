<a name="unreleased"></a>
## [Unreleased]

### Build
- **PHP:** bump PHP major version to min 7.4 (d5b1ff1)

### Chore
- **composer:** bump adhocore/cli (6647c9d)

### Docs
- **README:** fix typo of start command (657c71a)
- **Redis:** Fix phpdoc for Redis Component (70ac399)
- **Sponsor:** Add Sponsor `MeiHeZi` (ae612e0)
- **phpstorm:** Add `.phpstorm.meat.php` for config() function (407bb66)

### Feat
- **Requests:** Make Response Component extends from `Symfony\Component\HttpFoundation\Response` (dd001d2)
- **Requests:** Make Request Component extends from `Symfony\Component\HttpFoundation\Request` (9cd715b)
- **Torrent:** Per-add torrent edit (f06b342)
- **i18n:** Use symfony/translation and JSON format for locale (02cc251)
- **layout:** Add anti-robots html meta tag (9c21e73)

### Fix
- **Bencode:** Fix dict keys may not in sorted order (81f0783)
- **Config:** Fix JSON type config return False (1129008)
- **Cron:** Fix components lost in CronTabProcess (1ced4bf)
- **Redis:** Fix wrong type of Redis call function hMset() to hMSet() (a163150)
- **Route:** Fix user can access maintenance page even not on maintenance status (b29c2f6)
- **Tracker:** Fix typo in TrackerController (323c8ec)
- **Tracker:** Disable `retry in` feature by default (4b1f767)
- **User:** Fix all user class info become cur_user (77c7345)

### Perf
- **Component:** View Become a component to perf load time (660c1f1)
- **Log:** Separate Site Log Level from component Site (d110c2b)
- **Session_Log:** Use `insert with select` to log session (d786243)
- **Tracker:** Reduce Redis Calls for get User and Torrent Info in Tracker (e813435)
- **User:** Sort class User and create UserFactory (8fced36)
- **User:** Add `Entity\User\AbstractUserInterface` (1d8e9e4)
- **User:** Simple sql to get user real_transfer from table `snatched` (547c772)

### Refactor
- **Database:** Rename function creatCommand() to prepare() (09f6701)
- **Entity:** Separate Entity User, Torrent 's const to App\Repository (50ecdbf)
- **Entity:** Move Class Torrent,User to namespace App\Entity (7814d88)
- **Torrent:** Add TorrentFactory (e7dc26e)

### Style
- **Bencode:** Separate Bencode library to `Rhilip/Bencode` (18cbfa1)
- **cs-fixer:** Update Composer config and php-cs-fixer whole project (e734812)
- **gitignore:** Add .php_cs.cache to .gitignore (15a2a15)


<a name="v0.1.6-alpha"></a>
## [v0.1.6-alpha] - 2019-09-20
### Build
- **Validator:** Upgrade siriusphp/validation to 2.3 (eb039eb)

### Docs
- **Release:** Version 'v0.1.6-alpha' (e5a6e3e)
- **bin:** Add doc for bin/rid-httpd (59e0828)
- **template:** Add git commit hash in `CHANGELOG.md` (76bc527)

### Feat
- **Auth:** Use JWT to set cookies content (bf897c6)
- **Auth:** Sep Auth part from Site to new components (f36884e)
- **Auth:** Add Auth By passkey support for special route (aff1f87)
- **Auth/Login:** Add full Advanced Options support (6009dc8)
- **Bonus:** Pre-Add Bonus system (59ddd39)
- **Secret:** Check session and user_id match or not in jwt payload (358ba5d)
- **Secret:** Protect jwt key for env('APP_SECRET_KEY') (dfa67da)
- **Sessions:** record user access information at Auth->onRequestAfter() (e2a22a7)
- **Sessions/List:** Use SessionsListForm to show user sessions (9ecfb97)
- **Site:** Add page Site/{Logs,Rules} (65cea9e)
- **Torrent:** Show team, quality information in torrent/details page (44314e3)
- **Torrent/Download:** Add user download pos check (db6d5ff)
- **Torrent/Search:** Use MySQL fulltext search. (354c07b)
- **ban:** Sync site ban list of username and email in Crontab Jobs (33cc1e6)
- **ban_ips:** Store banned ip in components/Site (01084c9)
- **console:** use adhocore/cli to simple console application (a1bce3a)
- **torrents/tags:** Store torrent tags in TABLE `torrents` (4d573e2)

### Fix
- **Auth:** Fix user session can't storage in database (30b1049)
- **Auth:** Fix class check for route in AuthMiddleware failed (007d262)
- **Auth/Login:** Fix user can't login after commit `6009dc8` (d509127)
- **Component:** Fix parent::onRequest{Before,After} miss (200926f)
- **Search:** Search keywords in NPHP ways (ccde9c0)
- **Server:** Fix daemon and hot-reload not work (a206217)
- **Torrent:** Fix can't download torrent due to Declaration compatible (36588cc)
- **Torrent/Comment:** Fix user can't see anonymous uploader's comment (bd2d821)
- **User:** Fix User Class miss in string format (3680444)
- **fix:** Fix torrent can't upload after last commits (69fdce9)

### Perf
- **Auth/Login:** Simple The Auth Login Fail (6f11931)
- **IpBan:** Move ip ban list to runtime config (e0fb4f6)
- **JWT:** Short JWT payload key (7895158)
- **Process:** Disable Pdo And Redis called data in custom process (b744e81)
- **WorkerId:** Use getServ()->worker_id to Get workerId instead of set then getWorkerId() (bfdddde)

### Refactor
- **Array:** Move setDefault for Array as global function array_set_default (b825eca)
- **Auth:** Fix Certification process (687a2d0)
- **Auth/JWT:** Better for auth by JWT (36f49a0)
- **Auth/Middleware:** merge Old Auth{ByCookies, ByPasskey}Middleware (71cd7d7)
- **Config:** Add define of config key type and can add runtime config (d57aede)
- **Config:** Remove params `$throw` in Config()->get() (706cc9a)
- **Config:** Sort Config of `httpServer` and add Server hook (f47c458)
- **Controller:** Move APIController out Framework (0dc7106)
- **RateLimit:** Change last param of isRateLimitHit and rate limit store Namespace (4dd571d)
- **Site:** Simple Category Detail get function (ffa6855)
- **Site:** Move Cat, Quality, PinnedTag cache to Config.runtime (da1d9a7)
- **Validator:** fix user input extract (81bdc8f)
- **View:** Make View extends BaseObject (0865cf9)
- **action:** Sort template action/action_{fail,success} (66998d3)
- **torrent/structure:** Use zui.tree instead javascript `$(this).next('ul').toggle()` (7b20b2c)
- **view:** Fix helper/username params (720f37e)

### Revert
- **Framework:** rename back to `framework` instead of ucfirst() (c325fb0)
- **Redis:** Remove view in redis , use other software install (c5d3378)
- **app:** Backup folder name to `application` (19121a6)

### Style
- **Bencode:** Move Bencode library to App\Library but not part of framework (01abc98)
- **EnvironmentLoader:** Use Dotenv to load Loads environment variables (e6394a6)
- **Folder:** Rename folder name `<root>/private` to `<root>/storage` (f540d8f)
- **Nginx:** Rename Nginx Migration filename (108324e)
- **Redis:** rewrite namespace of cache keys (0c4e1a2)
- **dir:** rename folder `apps\` to `src\` (a01035c)
- **dir:** move apps\public to top dir (cb3beae)
- **env:** use $_ENV instead of getenv (2f5f0ac)
- **namespace:** `apps` to `App` with ucfirst... (8075d58)

### BREAKING CHANGE

Table `users` change

rename table `user_session_log` to `sessions` , add table `session_log`

User status 'banned' replace by 'disabled'


<a name="v0.1.5-alpha"></a>
## [v0.1.5-alpha] - 2019-08-09
### Build
- **Composer:** Update mirror address (e968948)

### Chore
- **gitignore:** Add ignore of `/backup` folder (83517b2)

### Docs
- **CHANGELOG:** fix git-chglog HeaderPattern (ddc474f)
- **Database:** Add miss table `subtitles` (29d7fa0)
- **release:** v0.1.5-alpha (eca61b1)

### Feat
- **Category:** Add Image and class_name support (6f4f318)
- **Category:** Add Categories Support when upload torrent (db9b99c)
- **Category:** Add Categories Manage Pane (77aba91)
- **Category:** Add Default sprite image of category (722eaab)
- **Crontab:** Move From Timer to Process (6ac4ff4)
- **Editor:** Support wysibb editor (c81b8c9)
- **File/Send:** Add Cache Control Headers support (5555dbb)
- **Gravatar:** Add support of gravatar (4252f8d)
- **Pager:** Torrents/{SearchForm,TagsForm} (07820ea)
- **Pager:** Add Pager Support (8dcd064)
- **Process:** Add custom Process Support (7057f26)
- **Process:** Clean Components before sleep (7497623)
- **RateLimit:** Add actionRateLimitCheckTrait (25b0520)
- **Redis:** Add mutiDelete() function for Redis (c9dc659)
- **Subtitle:** Add Base Subtitle Page (4fe52e5)
- **Subtitle/Delete:** Add Subtitle Delete support (e3a0b18)
- **Torrent/Form:** Add requests data autoload for Validator (ed31c17)
- **Tracker:** Move From Timer to Process (f2ab1b0)
- **Tracker:** Add `retry in` field when failed (5d18d4f)
- **User:** Add Bonus And Unread Messsage count (113ae95)
- **UserInfo:** Add Cache Lock of user access_{time,ip} update (bb9b623)
- **Validator:** Add autoload from requests function (16825fc)
- **ban:** Add table `ban_usernames` and `ban_emails` (7251651)
- **crontab:** Add torrent status sync (1e600a0)
- **csrf:** Add Csrf Support (9bddfa8)
- **email:** Use Site::sendEmail to simple email sender (b018663)
- **js/captcha:** add new random string to load captcha image (91a2e33)
- **js/scroll:** add scrollToTop (5536392)
- **system:** can get more system info via class SystemInfoHelper (6dc1028)
- **torrent/comment:** Prepare torrent comment field (164de39)
- **torrent/comments:** Add page torrent/comments?id= (2c8dbd3)
- **torrent/download:** Add multi tracker behaviour (837ba64)
- **torrent/nfo:** Show nfo in details page (a1f1d64)
- **torrent/snatch:** Add view of torrent/snatch (259c5f1)
- **torrent/tags:** Add tags show in page torrent_detail,torrents_list (f621685)
- **torrent/tags:** Add tags support of torrent upload (e198704)
- **torrent/upload:** Add base Quality Select Support (675fd0c)
- **torrent/upload:** Add teams support (b74982f)
- **torrent/upload:** Add nfo,hr support (a3eb839)
- **torrent/upload:** Add more config key to control behaviour (bb01120)
- **torrents:** Use Torrent Form Model in TorrentController (e786e9e)
- **torrents/tags:** Add tags page (10be2f8)
- **torrents/tags:** Add direct function to get pinned tags (21f81d0)
- **torrents/upload:** Add Filename Defend Checker (10ccd92)
- **upload/links:** Field of external resource link support (2ed6f8a)
- **user/bar:** Show partial seeding when eixst (d871952)
- **user/trait:** Use class cache, split real down,up,ratio function (886b207)
- **views/layout :** Add Quick Csrf Input refs (2c3a2fd)

### Fix
- **Anonymous:** Fix Auth Page 500 after commit `2cd1a499` (4e60ee8)
- **Auth/Login:** Fix User Can't Login (3ca05fc)
- **Cookies:** Fix session sep from `%` to `_` (f3e8e3f)
- **Database:** Fix table `links` miss (4578963)
- **Env:** Exit when parse env file failed (fc19504)
- **Form:** Link\EditForm Update diff (fd7b241)
- **Form:** Miss use flag since namespace change (77da0fd)
- **Pager:** Fix `Class define the same property in the composition of PagerTrait` (90dde55)
- **Requests:** Fix fullUrl() may add unnecessary `?` (722734c)
- **Site:** Fix old library Site not remove clean (9a18e20)
- **Torrent/Upload:** Fix Cannot use object as array (134153f)
- **Tracker:** Fix SQL error (abbef65)
- **Tracker:** Fix TrackerException Logger (b4543b1)
- **admin/redis_key:** Use print_r($v, true) instead of json_encode($v, JSON_PRETTY_PRINT) (3f767de)
- **categories:** Remove key `sort_index` (f8c1475)
- **class/cache:** Fix magic call may cause exception (5d1434c)
- **js/debug:** Fix may fail to parse sql debug data (3f31b88)
- **redis_key:** Fix array value cause parser error (4bf5dad)
- **security/validator:** Only assign user post data to the public props of class validator (d0dd439)
- **tags/search:** Fix search count and unique search tag redirect condition (a423577)
- **view/torrent_upload:** fix config key of upload flags (854722a)

### Perf
- **Category:** Remove apps/models/Category (d411770)
- **Site:** Move apps/{libraries->components}/Site (8620279)
- **Site:** try to cache re-hit (2cd1a49)
- **Tracker:** Use brpoplpush to get announce data from redis (8cac8fd)
- **captcha:** simple captcha input (9119bf7)
- **js/nav_active:** Active nav status frontend (c327d0e)
- **tracker:** No need to explicit serialize announce data (68e4550)
- **view/layout:** Judge visitor is anonymous or not in view (b54da2f)

### Refactor
- **Coroutine:** Remove Coroutine Model, Judge part (fc454c9)
- **Coroutine:** Remove Coroutine Model (87b12e3)
- **File/Download:** Seperate client download file function to FileDownloadTrait (456ea04)
- **Pager:** Separate Pager as Trait (4442bb7)
- **Torrent/Download:** Make multi tracker behaviour more readable (e366938)
- **Torrent/Nfo:** move nfoConvert from api model to class Torrent (0736c63)
- **Tracker:** Better Tracker behaviour in multi tracker (1477ced)
- **UserInfo:** fix last_access_{ip,at} update time (e9b8d8d)
- **View:** Rename folder `error` to `action` (f5344af)
- **action_success:** Simple The Action Template (9facda1)
- **array/function:** move setDefault to \Rid class (b49d529)
- **auth/error:** merge `auth/error` page to `action/action_success` (9f02aae)
- **class/cache:** Use trait to simple class value cache (9913873)
- **site/torrent_upload_rule:** Move rule loader to \library\Site::class (ed01663)
- **torrent/download:** Separate Torrent::getDownloadDict to torrent\DownloadForm (4b617ca)
- **torrent/snatch:** Separate Torrent::getSnatchDetails to torrent\SnatchForm Pager (853a743)
- **torrents/upload:** check info_hash in valid function but not flush (32967c4)

### Style
- **Auth:** Sort Auth Form (35691fc)
- **Form:** Sort Forms (ae72ce3)
- **fix/typo:** fix typo about word 'multi' from word 'muti' (8821b08)
- **js:** merge separate js file to `main.js` (4d641ee)
- **printIn:** Add datetime tag (689b922)

### BREAKING CHANGE

COLUMN `corrupt` in `users` TABLE changed

Rename Table `ip_bans` to `ban_ips`

Table `torrents_categories` rename to `categories`

Table `torrents` structure change


<a name="v0.1.4-alpha"></a>
## [v0.1.4-alpha] - 2019-06-28
### Chore
- **User:** Make User component as Part of App but not framework (233e62d)

### Docs
- **Licence:** Add Licence Checker By FOSSA (295205c)
- **Migration:** Update Nginx config (900c999)
- **README:** Fix Mailer Env typo (1c1f31b)
- **release:**  v0.1.4-alpha (fb8c96f)

### Feat
- **Auth:** Add UserRecover Form (ecf68a9)
- **Auth:** Add full Advanced Options for Login (a184a65)
- **Cleanup:** Add disable cleanup job by set priority to 0 (be94de3)
- **Cleanup:** Cleanup dead peers (0d83408)
- **Debug:** Add extend debug output (9d4f0cb)
- **Debug:** Remove DebugMiddleware (07c7fd1)
- **Email:** Add email template (af4c102)
- **Form Validator:** Add library bootstrap-validator library (2476921)
- **Frontend:** Add localforage As Cache Provider (f830f22)
- **Invite:** Finish invite system (904476c)
- **Invite:** Add base invite table (c556644)
- **Links:** Add full Links manage (68a5ac5)
- **News:** Fix news tag not appear in index (51dfebc)
- **News:** Add Site News model (e9397fb)
- **Register:** Can't copy password and paste to retyep_password (2cb15e6)
- **Response:** Add redirect code (5d8742d)
- **Task:** Add task process support (241ef8d)
- **Timer:** Add Timer Example (4413b46)
- **Torrent:** Add folder size sum in torrent file modal (3072c39)
- **Typo:** Fix Typo of `secret` (f3f6206)
- **framework:** Remove Swoole Task Support (8188df9)

### Fix
- **Admin Panel:** Fix index num in Redis Keys Page (b8ca4c4)
- **Auth:** Fix Broken Auth page after frontend framework change (21dca47)
- **Captcha:** Fix style of captcha (a43f9be)
- **DATABASE:** Fix CURRENT_TIMESTAMP Extra error (096333b)
- **Email:** Fix Email Can't Send (5e5634e)
- **Error:** Fix Error Handler (3fd6821)
- **Invite:** Add Fine-grained control of recycle pending (c56a705)
- **Links:** Remove extra meta section (50f0728)
- **Register:** Add captcha checker (bacee6d)
- **TorrentUpload:** Fix length 0 file cause ParseErrorException (e580577)
- **Tracker:** Add miss port check for field ipv6_port (ff6351d)
- **View:** Fix Conversion::setDefault() (bec2fc0)
- **View:** remove view helper function `get_torrent_uploader_id` (65f09ff)
- **View:** Fix view function redeclare (81c8aff)

### Perf
- **BBCode:** Use mjohnson/decoda to parse and cache BBCode (fbc98ad)
- **Config:** Add quick ref config() (ae2a359)
- **Config:** Remove `configTable_construct_lock` (a9cd0b9)
- **DATABASE:** Drop Table `files` (c3e329d)
- **Tracker:** Use Task process to quick response Tracker Announce Action (671a551)
- **Tracker:** Add passkey and info_hash Filter (c28d695)

### Refactor
- **InviteAction:** Use InviteActionFrom instead of func in Controller (7fa7069)
- **Mailer:** Use phpmailer to replace swiftmailer (c7e66db)
- **Redis:** Add more Redis arguments in debug output (8b887f8)
- **Session:** Add Session Format Docs (a2a1ce1)
- **SiteConfig:** change namespace `authority.route_` to `route.` (037ca71)
- **Tracker:** Separate announce data update function (22d69bc)
- **Validator:** Add function buildDefaultValue() (d263c6c)
- **ext2Icon:** Add more File format (e8c9962)
- **timer:** Change Namespace of Timer (766d823)

### Revert
- **Http:** Remove Request::getUserAgent() (ada223c)

### Style
- **Admin Panel:** Fix Broken View Admin Panel (e834831)
- **Auth:** Not output extend debug info for anonymous. (In route `/auth`) (152331b)
- **Frontend:** Back frontend framework to Zui (b08c2ed)
- **View:** Add top util by layui.util (282d5a4)

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
- **Compatible:** Remove Compatible Model (f1ca9d9)
- **Environment:** Upgrade Dependency of Mysql and PHP (4324780)

### Chore
- **Git:** Fix gitkeep file lost (6c995d8)
- **Server:** Change the server printing (ec68e4a)
- **Static File:** Move fonts to public path (7c61422)

### Docs
- **Debug:** End Error Handle Debug (94906eb)
- **Demo:** Change demo site link (0eed6a0)
- **Readme:** Add Demo Site Information (f599825)
- **Readme:** Update Readme.md (7ae98b5)
- **Release:** Release v0.1.3-alpha (8b4031b)

### Feat
- **Favour:** Add full favour support (1c83715)
- **Framework:** Add Record of execute sql and redis key hit (4799813)
- **Front:** Add lib `notice.js` (a4fcfff)
- **Helper:** Add Simple String crypt helper (ef5fab2)
- **RSS:** Start Build rss feed (4e38460)
- **Torrent:** Save Torrent File Structure in Table `torrents` (96e110e)
- **Torrent:** Clean temp upload torrent file after success upload (ffca1e7)
- **Torrent:** Add Torrent FileList View (3101a63)
- **Torrent:** Finish Torrent's Structure part (b95112b)
- **Torrent:** Add Torrent Content Cache (0038a7d)
- **Torrent Upload:** Add utf-8 path support for torrent upload (1901e17)
- **UBB:** Add ubb converter (59ffa46)
- **User:** Add User Active Seed/Leech Count (e22b04c)
- **User:** Add User Confirm Support (9277978)
- **User:** Add user load by passkey (b93d94c)
- **View:** Add ram use status in footer (ec415ac)
- **i18n:** Add i18n Support (2a48b4c)

### Fix
- **Auth:** Fix layout fo Auth Point (7f36ddf)
- **Auth:** Fix 'errors/action_fail' view should not be touch in AuthController (e2f1bbb)
- **Auth:** Update description texts in `/auth/login` (cbff703)
- **Bencode:** Add full parse check (a24638b)
- **Compatible:** Fix Compatible Model not work (a1fb974)
- **Database:** Change Foreign Key behaviour (e263e07)
- **Nameplace:** Fix error nameplace of compatible (3daf17b)
- **Namespace:** Namespace miss change (4262301)
- **Torrent:** Fix Torrent Download Dict miss (3a1780b)
- **Upload:** Fix Upload Torrent can't save in database (26d3902)
- **UserTrackerStatus:** Fix redis cache key miss (0ad2d64)
- **View:** Fix View Don't change after service reload (2119c2c)
- **View:** Fix `admin/redis_key` of wrong array echo (6447136)
- **View:** Fix cost time miss in render (9cd155b)

### Perf
- **Admin:** Call redis key info only when pattern give (22b1e80)
- **Bencode:** Remove Bencode Library `sandfoxme/bencode` (0e1a6ab)
- **Config:** Create \Swoole\Table as Dynamic Config Provider in Master Process (0a7bfba)
- **Error:** Fix Error Page (c58c8bb)
- **Mailer:** Make Mailer Provider extends BaseObject but not Component (6dc635a)
- **Middleware:** Fix Middleware behaviour (eb9c905)
- **Psr:** Use Psr\Log to simple Rid\Log Component (342300f)
- **Session:** Not resend set-cookies header for session (8fa31bc)
- **User:** Quickly get user peer status (020a96f)
- **Validator:** load base rules from call parent method (9d7191b)
- **i18n:** Quick load (20b8a15)

### Refactor
- **DynamicConfig:** Remove DynamicConfig provider by Redis (d644d0b)
- **Framework:** Resort Component folder (80c7608)
- **Framework:** Rename Mix to Rid (9c0350d)
- **Framework:** Refactor framework from upstream (605e111)
- **Helpers:** Remove JsonHelper (df5d57e)
- **Output:** Remove \Rid\Console\Output (2ffc1ac)
- **Route:** Fix Route of Torrent (c2282ec)
- **Validator:** Change Validator Provider (6d56295)
- **View:** Change template system (ae2a7b7)
- **XML:** Remove XML support (169440c)

### Revert
- **CSS_TAG:** re-add debug for css_tag (e143301)
- **Config:** Add back Dynamic Config provider by Redis (043ad5c)
- **UploadFile:** Add back some attributes of UploadFile (f6f9e6c)

### Style
- **CSS:** Add css tag (c6e9211)
- **Git:** Fix git style in private path (5bffbd9)
- **Validator:** Separate File and Captcha Validate to Trait object (58b86ac)
- **View:** Change View Layout (ac91bf3)

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
- **Release:** Release v0.1.2-alpha (3db0f5e)
- **changelog:** Use `git-chglog` to generate CHANGELOG.md (ac461b0)

### Feat
- **Login:** Add Support for max login ip test to avoid floor attack (961ff1e)
- **User:** User can manager their own sessions (f3c38a7)

### Fix
- **Auth:** Fix may redirect twice in `/auth/login` (83cac6e)
- **Auth:** Fix Login redirect too much (18eed6d)

### Refactor
- **Table:** use `agent` to replace `browser` and `platform` (ef846a0)

### Style
- **Admin Panel:** replace _ to backspace in render (857dc20)

### BREAKING CHANGE

Structure of Table `users_session_log` Change


<a name="v0.1.1-alpha"></a>
## [v0.1.1-alpha] - 2019-01-31

<a name="v0.1.0-alpha"></a>
## v0.1.0-alpha - 2019-01-30

[Unreleased]: https://github.com/Rhilip/ridpt/compare/v0.1.6-alpha...HEAD
[v0.1.6-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.5-alpha...v0.1.6-alpha
[v0.1.5-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.4-alpha...v0.1.5-alpha
[v0.1.4-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.3-alpha...v0.1.4-alpha
[v0.1.3-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.2-alpha...v0.1.3-alpha
[v0.1.2-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.1-alpha...v0.1.2-alpha
[v0.1.1-alpha]: https://github.com/Rhilip/ridpt/compare/v0.1.0-alpha...v0.1.1-alpha
