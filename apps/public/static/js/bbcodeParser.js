/*
Edited from https://github.com/patorjk/Extendible-BBCode-Parser

By Rhilip, 2018/9/24
*/

var XBBCODE = (function() {
    "use strict";

    // -----------------------------------------------------------------------------
    // Set up private variables
    // -----------------------------------------------------------------------------

    var urlPattern = /^[-a-zA-Z0-9:;,@#%&()~_?+=\/\\.]*$/,
        colorPattern = /^(?:[a-z]+|#[0-9abcdef]+)$/,
        fontFacePattern = /^([A-Za-z][A-Za-z0-9_\s]+|"[A-Za-z][A-Za-z0-9_\s]+")$/i;

    /* -----------------------------------------------------------------------------
     * tags
     * This object contains a list of tags that your code will be able to understand.
     * Each tag object has the following properties:
     *
     *   openTag - A function that takes in the tag's parameters (if any) and its
     *             contents, and returns what its HTML open tag should be.
     *             Example: [color=red]test[/color] would take in "=red" as a
     *             parameter input, and "test" as a content input.
     *             It should be noted that any BBCode inside of "content" will have
     *             been processed by the time it enter the openTag function.
     *
     *   closeTag - A function that takes in the tag's parameters (if any) and its
     *              contents, and returns what its HTML close tag should be.
     *
     *   displayContent - Defaults to true. If false, the content for the tag will
     *                    not be displayed. This is useful for tags like IMG where
     *                    its contents are actually a parameter input.
     *
     *   restrictChildrenTo - A list of BBCode tags which are allowed to be nested
     *                        within this BBCode tag. If this property is omitted,
     *                        any BBCode tag may be nested within the tag.
     *
     *   restrictParentsTo - A list of BBCode tags which are allowed to be parents of
     *                       this BBCode tag. If this property is omitted, any BBCode
     *                       tag may be a parent of the tag.
     *
     *   noParse - true or false. If true, none of the content WITHIN this tag will be
     *             parsed by the XBBCode parser.
     *
     *
     *
     * LIMITIONS on adding NEW TAGS:
     *  - Tag names should be alphanumeric (including underscores) and all tags should have an opening tag
     *    and a closing tag.
     *    The [*] tag is an exception because it was already a standard
     *    bbcode tag. Technecially tags don't *have* to be alphanumeric, but since
     *    regular expressions are used to parse the text, if you use a non-alphanumeric
     *    tag names, just make sure the tag name gets escaped properly (if needed).
     * --------------------------------------------------------------------------- */

    var defaultTags = {
        "hr": { openTag: '<hr />', closeTag: '' , displayContent: false},

        "b": { openTag: '<b>', closeTag: '</b>' },
        "i": { openTag: '<i>', closeTag: '</i>' },
        "u": { openTag: '<u>', closeTag: '</u>' },
        "s": { openTag: '<s>', closeTag: '</s>' },
        "pre": { openTag: '<pre>', closeTag: '</pre>' },
        "sub": { openTag: '<sub>', closeTag: '</sub>' },
        "sup": { openTag: '<sup>', closeTag: '</sup>' },
        "center": { openTag: '<div style="text-align: center;">', closeTag: '</div>' },

        "code": { openTag: '<br /><div class="codetop"> Code </div><div class="codemain">', closeTag: '</div><br />', noParse: true },
        "noparse": { openTag: '', closeTag: '', noParse: true },

        "em": {
            openTag: function (params, content) {
                return '<img src="pic/smilies/' + content + '.gif" alt="em' + content + '" />';
            },
            closeTag: '',
            displayContent: false
        },
        "img": {
            openTag: function (params, content) {
                return '<img src="' + content + '" style="max-width: 100%"/>';
            },
            closeTag: '',
            displayContent: false
        },
        "color": {
            openTag: function (params) {
                params = params || '';

                var colorCode = (params.substr(1)).toLowerCase() || "black";
                if (!colorPattern.test(colorCode)) {
                    colorCode = "black";
                }

                return '<span style="color:' + colorCode + '">';
            },
            closeTag: '</span>'
        },
        "font": {
            openTag: function (params, content) {
                params = params || '';

                var faceCode = params.substr(1) || "monospace";
                fontFacePattern.lastIndex = 0;
                if (!fontFacePattern.test(faceCode)) {
                    faceCode = "monospace";
                }
                return '<span style="font-family:' + faceCode + '">';
            },
            closeTag: '</span>'
        },
        "size": {
            openTag: function(params,content) {
                params = params || '';

                var mySize = parseInt(params.substr(1),10) || 2;
                if (mySize < 0 || mySize > 40) {
                    mySize = 2;
                }

                return '<font size="' + mySize + '">';
            },
            closeTag: '</font>'
        },

        "quote": {
            openTag: function (params, content) {
                return '<fieldset><legend> Quote ' + ((params && params.length > 1) ? (': ' + params.substr(1)) : "") + '</legend><br />';
            },
            closeTag: '</fieldset><br />'
        },
        "hide": {
            openTag: function (params, content) {
                return "<div class=\"codetop\"><a class=\"expand nowrap\" href=\"javascript:\" title=\"Show/Hide\"><img class=\"plus\" src=\"pic/trans.gif\" alt=\"Show/Hide\" />&nbsp;<span>Show</span></a></div><div class=\"codemain\" style=\"display: none;\">";
            },
            closeTag: "</div>"
        },
        "url": {
            openTag: function(params,content) {
                var myUrl = !params ? content.replace(/<.*?>/g, "") : params.substr(1);
                if (!urlPattern.test(myUrl)) {
                    myUrl = "#";
                }

                return '<a href="' + myUrl + '" target="_blank" class="faqlink">';
            },
            closeTag: '</a>'
        },

        /*
            The [*] tag is special since the user does not define a closing [/*] tag when writing their bbcode.
            Instead this module parses the code and adds the closing [/*] tag in for them. None of the tags you
            add will act like this and this tag is an exception to the others.
        */
        "list": { openTag: '<ul>', closeTag: '</ul>', restrictChildrenTo: ["*", "li"] },
        "li": { openTag: "<li>", closeTag: "</li>", restrictParentsTo: ["list","ul","ol"] },
        "ol": { openTag: '<ol>', closeTag: '</ol>', restrictChildrenTo: ["*", "li"] },
        "ul": { openTag: '<ul>', closeTag: '</ul>', restrictChildrenTo: ["*", "li"] },
        "*": { openTag: "<li>", closeTag: "</li>"},

        "table": { openTag: '<table class="main">', closeTag: '</table>', restrictChildrenTo: ["tbody","thead", "tfoot", "tr"] },
        "tbody": { openTag: '<tbody>', closeTag: '</tbody>', restrictChildrenTo: ["tr"], restrictParentsTo: ["table"] },
        "tfoot": { openTag: '<tfoot>', closeTag: '</tfoot>', restrictChildrenTo: ["tr"], restrictParentsTo: ["table"] },
        "thead": { openTag: '<thead>', closeTag: '</thead>', restrictChildrenTo: ["tr"], restrictParentsTo: ["table"] },
        "td": { openTag: '<td>', closeTag: '</td>', restrictParentsTo: ["tr"] },
        "th": { openTag: '<th>', closeTag: '</th>', restrictParentsTo: ["tr"] },
        "tr": { openTag: '<tr>', closeTag: '</tr>', restrictChildrenTo: ["td","th"], restrictParentsTo: ["table","tbody","tfoot","thead"] }
    };

    function Parser() {
        this._tags = {};
        this._tagList = [];
        this._tagsNoParseList = [];
        this._bbRegExp = null;
        this._pbbRegExp = null;
        this._pbbRegExp2 = null;
        this._openTags = null;
        this._closeTags = null;
        this._initialized = false;
    }

    // create tag list and lookup fields
    Parser.prototype._initTags = function() {
        var self = this;
        this._tagList = [];
        var tags = this._tags;
        var prop,
            ii,
            len;
        for (prop in tags) {
            if (tags.hasOwnProperty(prop)) {
                if (prop === "*") {
                    this._tagList.push("\\" + prop);
                } else {
                    this._tagList.push(prop);
                    if ( tags[prop].noParse ) {
                        this._tagsNoParseList.push(prop);
                    }
                }

                tags[prop].validChildLookup = {};
                tags[prop].validParentLookup = {};
                tags[prop].restrictParentsTo = tags[prop].restrictParentsTo || [];
                tags[prop].restrictChildrenTo = tags[prop].restrictChildrenTo || [];

                len = tags[prop].restrictChildrenTo.length;
                for (ii = 0; ii < len; ii++) {
                    tags[prop].validChildLookup[ tags[prop].restrictChildrenTo[ii] ] = true;
                }
                len = tags[prop].restrictParentsTo.length;
                for (ii = 0; ii < len; ii++) {
                    tags[prop].validParentLookup[ tags[prop].restrictParentsTo[ii] ] = true;
                }
            }
        }

        this._bbRegExp = new RegExp("<bbcl=([0-9]+) (" + this._tagList.join("|") + ")([ =][^>]*?)?>((?:.|[\\r\\n])*?)<bbcl=\\1 /\\2>", "gi");
        this._pbbRegExp = new RegExp("\\[(" + this._tagList.join("|") + ")([ =][^\\]]*?)?\\]([^\\[]*?)\\[/\\1\\]", "gi");
        this._pbbRegExp2 = new RegExp("\\[(" + this._tagsNoParseList.join("|") + ")([ =][^\\]]*?)?\\]([\\s\\S]*?)\\[/\\1\\]", "gi");

        // create the regex for escaping ['s that aren't apart of tags
        (function() {
            var closeTagList = [];
            for (var ii = 0; ii < self._tagList.length; ii++) {
                if ( self._tagList[ii] !== "\\*" ) { // the * tag doesn't have an offical closing tag
                    closeTagList.push ( "/" + self._tagList[ii] );
                }
            }

            self._openTags = new RegExp("(\\[)((?:" + self._tagList.join("|") + ")(?:[ =][^\\]]*?)?)(\\])", "gi");
            self._closeTags = new RegExp("(\\[)(" + closeTagList.join("|") + ")(\\])", "gi");
        })();
        this._initialized = true;
    };

    // -----------------------------------------------------------------------------
    // private functions
    // -----------------------------------------------------------------------------

    Parser.prototype._checkParentChildRestrictions = function(parentTag, bbcode, bbcodeLevel, tagName,
                                                              tagParams, tagContents, errQueue) {
        var self = this;
        errQueue = errQueue || [];
        bbcodeLevel++;

        // get a list of all of the child tags to this tag
        var reTagNames = new RegExp("(<bbcl=" + bbcodeLevel + " )(" + this._tagList.join("|") + ")([ =>])","gi"),
            reTagNamesParts = new RegExp("(<bbcl=" + bbcodeLevel + " )(" + this._tagList.join("|") + ")([ =>])","i"),
            matchingTags = tagContents.match(reTagNames) || [],
            cInfo,
            errStr,
            ii,
            childTag,
            pInfo = this._tags[parentTag] || {};

        reTagNames.lastIndex = 0;

        if (!matchingTags) {
            tagContents = "";
        }

        for (ii = 0; ii < matchingTags.length; ii++) {
            reTagNamesParts.lastIndex = 0;
            childTag = (matchingTags[ii].match(reTagNamesParts))[2].toLowerCase();

            if ( pInfo && pInfo.restrictChildrenTo && pInfo.restrictChildrenTo.length > 0 ) {
                if ( !pInfo.validChildLookup[childTag] ) {
                    errStr = "The tag \"" + childTag + "\" is not allowed as a child of the tag \"" + parentTag + "\".";
                    errQueue.push(errStr);
                }
            }
            cInfo = this._tags[childTag] || {};
            if ( cInfo.restrictParentsTo.length > 0 ) {
                if ( !cInfo.validParentLookup[parentTag] ) {
                    errStr = "The tag \"" + parentTag + "\" is not allowed as a parent of the tag \"" + childTag + "\".";
                    errQueue.push(errStr);
                }
            }

        }

        tagContents = tagContents.replace(this._bbRegExp, function(matchStr, bbcodeLevel, tagName, tagParams, tagContents ) {
            errQueue = self._checkParentChildRestrictions(tagName.toLowerCase(), matchStr, bbcodeLevel, tagName, tagParams, tagContents, errQueue);
            return matchStr;
        });
        return errQueue;
    };

    /*
        This function updates or adds a piece of metadata to each tag called "bbcl" which
        indicates how deeply nested a particular tag was in the bbcode. This property is removed
        from the HTML code tags at the end of the processing.
    */
    Parser.prototype._updateTagDepths = function(tagContents) {
        tagContents = tagContents.replace(/\<([^\>][^\>]*?)\>/gi, function(matchStr, subMatchStr) {
            var bbCodeLevel = subMatchStr.match(/^bbcl=([0-9]+) /);
            if (bbCodeLevel === null) {
                return "<bbcl=0 " + subMatchStr + ">";
            } else {
                return "<" + subMatchStr.replace(/^(bbcl=)([0-9]+)/, function(matchStr, m1, m2) {
                    return m1 + (parseInt(m2, 10) + 1);
                }) + ">";
            }
        });
        return tagContents;
    };

    /*
        This function removes the metadata added by the updateTagDepths function
    */
    Parser.prototype._unprocess = function(tagContent) {
        return tagContent.replace(/<bbcl=[0-9]+ \/\*>/gi,"").replace(/<bbcl=[0-9]+ /gi,"&#91;").replace(/>/gi,"&#93;");
    };

    Parser.prototype._parse = function(config) {
        var self = this;
        var output = config.text;

        var replaceFunct = function(matchStr, bbcodeLevel, tagName, tagParams, tagContents) {
            tagName = tagName.toLowerCase();

            var processedContent = self._tags[tagName].noParse ? self._unprocess(tagContents) : tagContents.replace(self._bbRegExp, replaceFunct),
                openTag = typeof self._tags[tagName].openTag === "function" ? self._tags[tagName].openTag(tagParams,processedContent) : self._tags[tagName].openTag,
                closeTag = typeof self._tags[tagName].closeTag === "function" ? self._tags[tagName].closeTag(tagParams,processedContent) : self._tags[tagName].closeTag;

            if (self._tags[tagName].displayContent === false) {
                processedContent = "";
            }

            return openTag + processedContent + closeTag;
        };

        output = output.replace(this._bbRegExp, replaceFunct);
        return output;
    };

    /*
        The star tag [*] is special in that it does not use a closing tag. Since this parser requires that tags to have a closing
        tag, we must pre-process the input and add in closing tags [/*] for the star tag.
        We have a little levaridge in that we know the text we're processing wont contain the <> characters (they have been
        changed into their HTML entity form to prevent XSS and code injection), so we can use those characters as markers to
        help us define boundaries and figure out where to place the [/*] tags.
    */
    Parser.prototype._fixStarTag = function(text) {
        text = text.replace(/\[(?!\*[ =\]]|list([ =][^\]]*)?\]|\/list[\]])/ig, "<");
        text = text.replace(/\[(?=list([ =][^\]]*)?\]|\/list[\]])/ig, ">");

        while (text !== (text = text.replace(/>list([ =][^\]]*)?\]([^>]*?)(>\/list])/gi, function(matchStr,contents,endTag) {

            var innerListTxt = matchStr;
            while (innerListTxt !== (innerListTxt = innerListTxt.replace(/\[\*\]([^\[]*?)(\[\*\]|>\/list])/i, function(matchStr,contents,endTag) {
                if (endTag.toLowerCase() === ">/list]") {
                    endTag = "</*]</list]";
                } else {
                    endTag = "</*][*]";
                }
                return "<*]" + contents + endTag;
            })));

            innerListTxt = innerListTxt.replace(/>/g, "<");
            return innerListTxt;
        })));

        // add ['s for our tags back in
        text = text.replace(/</g, "[");
        return text;
    };

    Parser.prototype._addBbcodeLevels = function(text) {
        var self = this;
        while ( text !== (text = text.replace(this._pbbRegExp, function(matchStr, tagName, tagParams, tagContents) {
            matchStr = matchStr.replace(/\[/g, "<");
            matchStr = matchStr.replace(/\]/g, ">");
            return self._updateTagDepths(matchStr);
        })) );
        return text;
    };

    // -----------------------------------------------------------------------------
    // public functions
    // -----------------------------------------------------------------------------

    // API, Expose all available tags
    Parser.prototype.tags = function() {
        return this._tags;
    };

    // API
    Parser.prototype.addTags = function(newtags) {
        var tag;
        for (tag in newtags) {
            this._tags[tag] = newtags[tag];
        }
        this._initTags();
    };

    Parser.prototype.addDefaultTags = function() {
        this.addTags(defaultTags);
    };

    Parser.prototype.process = function(config) {
        if (!this._initialized) {
            throw new Error('Tags are not initialized');
        }
        var ret = { html: "", error: false }, errQueue = [];

        // Replace Old tag before Convert
        config.text = config.text.replace(/\[site]\[\/site]/g,"[site]").replace(/\[site]/g,"[site][/site]");
        config.text = config.text.replace(/\[siteurl]\[\/siteurl]/g,"[siteurl]").replace(/\[siteurl]/g,"[siteurl][/siteurl]");
        config.text = config.text.replace(/\[em(\d+)]/g,"[em]$1[/em]");
        config.text = config.text.replace(/\[hr[ /]{1,2}]/g,"[hr]No parser[/hr]");

        // escape HTML tag brackets
        config.text = config.text.replace(/</g, "&lt;");
        config.text = config.text.replace(/>/g, "&gt;");

        config.text = config.text.replace(this._openTags, function(matchStr, openB, contents, closeB) {
            return "<" + contents + ">";
        });
        config.text = config.text.replace(this._closeTags, function(matchStr, openB, contents, closeB) {
            return "<" + contents + ">";
        });

        config.text = config.text.replace(/\[/g, "&#91;"); // escape ['s that aren't apart of tags
        config.text = config.text.replace(/\]/g, "&#93;"); // escape ['s that aren't apart of tags
        config.text = config.text.replace(/</g, "["); // escape ['s that aren't apart of tags
        config.text = config.text.replace(/>/g, "]"); // escape ['s that aren't apart of tags
        config.text = config.text.replace(/ /g,"&ensp;");  // escape whitespace

        // process tags that don't have their content parsed
        while ( config.text !== (config.text = config.text.replace(this._pbbRegExp2, function(matchStr, tagName, tagParams, tagContents) {
            tagContents = tagContents.replace(/\[/g, "&#91;");
            tagContents = tagContents.replace(/\]/g, "&#93;");
            tagParams = tagParams || "";
            tagContents = tagContents || "";
            return "[" + tagName + tagParams + "]" + tagContents + "[/" + tagName + "]";
        })) );

        config.text = this._fixStarTag(config.text); // add in closing tags for the [*] tag
        config.text = this._addBbcodeLevels(config.text); // add in level metadata

        errQueue = this._checkParentChildRestrictions("bbcode", config.text, -1, "", "", config.text);

        ret.html = this._parse(config);

        if ( ret.html.indexOf("[") !== -1 || ret.html.indexOf("]") !== -1) {
            errQueue.push("Some tags appear to be misaligned, We will try to fix it.");
            ret.html = ret.html.replace(/\[color=([A-Za-z]+|#[0-9abcdef]+)]/g,'<span style="color: $1">').replace(/\[\/color]/g,"</span>");
            ret.html = ret.html.replace(/\[font=([A-Za-z][A-Za-z0-9_\s]+|"[A-Za-z][A-Za-z0-9_\s]+")]/g,'<span style="font-family: $1">').replace(/\[\/font]/g,"</span>");
            ret.html = ret.html.replace(/\[size=(\d+)]/g,'<font size="$1">').replace(/\[\/size]/g,"</font>");
            ret.html = ret.html.replace(/\[([bius])]/g,"<$1>").replace(/\[\/([bius])]/g,"</$1>");
            ret.html = ret.html.replace(/\[\*]/g,'<img class="listicon listitem" src="pic/trans.gif" alt="list" />');

            var left_quote_match = ret.html.match(/\[quote.*?]/g), right_quote_match = ret.html.match(/\[\/quote]/g);
            if (left_quote_match && right_quote_match && left_quote_match.length === right_quote_match.length) {
                ret.html = ret.html.replace(/\[quote]/g,'<fieldset><legend> Quote </legend><br />');
                ret.html = ret.html.replace(/\[quote=(.+?)]/g,'<fieldset><legend> Quote : $1 </legend><br />');
                ret.html = ret.html.replace(/\[\/quote]/g,'</fieldset><br />');
            }
        }

        if (config.removeMisalignedTags) {
            ret.html = ret.html.replace(/\[.*?\]/g,"");
        }
        if (config.addInLineBreaks) {
            ret.html = '<div style="white-space:pre-wrap;">' + ret.html + '</div>';
        }

        if (!config.escapeHtml) {
            ret.html = ret.html.replace("&#91;", "["); // put ['s back in
            ret.html = ret.html.replace("&#93;", "]"); // put ['s back in
        }

        ret.html = ret.html.replace(/\n/g,"<br />");

        ret.error = errQueue.length !== 0;
        ret.errorQueue = errQueue;

        return ret;
    };

    var parser = {};
    parser = new Parser();
    parser.addDefaultTags();
    parser.Parser = Parser;

    return parser;
})();
