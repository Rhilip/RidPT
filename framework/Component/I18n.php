<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/13
 * Time: 19:55
 */

namespace Rid\Component;

use Symfony\Component\Translation\Translator;

class I18n
{
    /**
     * Allowed language
     * This is the set of language which is used to limit user languages. No-exist language will not accept.
     *
     * @var array
     */
    public array $allowedLangSet = ['en', 'zh-CN'];

    /**
     * Forced language
     * If you want to force a specific language define it here.
     *
     * @var string
     */
    public ?string $forcedLang = null;

    /** @var Translator */
    protected ?Translator $_translator;

    public function __construct(Translator $_translator)
    {
        $this->_translator = $_translator;
    }

    /**
     * getUserLangs()
     * Returns the user languages
     * Normally it returns an array like this:
     *     1. Language in $_GET['lang']
     *     2. Language in user setting
     *     3. HTTP_ACCEPT_LANGUAGE
     * Note: duplicate values are deleted.
     *
     * @return string the user languages sorted by priority.
     */
    public function getUserLang()
    {
        // Return Cache value
        if (!is_null(app()->request->attributes->get('user_lang'))) {
            return app()->request->attributes->get('user_lang');
        }

        // Determine
        $judged_langs = array();

        // 1nd highest priority: GET parameter 'lang'
        if (!is_null(app()->request->query->get('lang'))) {
            $judged_langs[] = app()->request->query->get('lang');
        }

        // 2rd highest priority: user setting for login user
        if (app()->auth->getCurUser() && !is_null(app()->auth->getCurUser()->getLang())) {
            $judged_langs[] = app()->auth->getCurUser()->getLang();
        }

        // 3th highest priority: HTTP_ACCEPT_LANGUAGE
        if (!is_null(app()->request->headers->get('accept_language'))) {
            /**
             * We get headers like this string 'en-US,en;q=0.8,uk;q=0.6'
             * And then sort to an array like this after sort
             *
             * array(size=4)
             *    'en-US'    => float 1
             *    'en'       => float 0.8
             *    'uk'       => float 0.6
             *
             */
            $prefLocales = array_reduce(
                explode(',', app()->request->headers->get('accept_language')),
                function ($res, $el) {
                    list($l, $q) = array_merge(explode(';q=', $el), [1]);
                    $res[$l] = (float)$q;
                    return $res;
                },
                []
            );
            arsort($prefLocales);

            foreach ($prefLocales as $part => $q) {
                $judged_langs[] = $part;
            }
        }

        $userLangs = array_intersect(
            array_unique($judged_langs),   // remove duplicate elements
            $this->allowedLangSet
        );

        foreach ($userLangs as $lang) {
            app()->request->attributes->set('user_lang', $lang);  // Store it for last use if not in req mode
            return $lang;
        }
        return null;
    }

    /**
     * Get i18n text by call static constant, if the string is not exist. The empty string ''
     * will be return.
     *
     * @param string $string the trans string
     * @param array $args the args used for format string
     * @param string|null $domain The domain for the message or null to use the default
     * @param string|null $required_lang the required lang
     * @return string
     */
    public function trans($string, $args = [], $domain = null, $required_lang = null)
    {
        $local =
            $this->forcedLang ?? // Highest priority: forced language
            $required_lang ??    // 1st highest priority: required language
            $this->getUserLang();

        return $this->_translator->trans($string, $args, $domain, $local);
    }
}
