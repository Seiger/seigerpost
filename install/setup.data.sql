--
-- Table structure `{PREFIX}s_post_contents`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}s_post_contents` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `published` tinyint(4) NOT NULL,
    `author` int(11) NOT NULL DEFAULT 0,
    `alias` varchar(512) NOT NULL,
    `cover` varchar(512) NOT NULL,
    `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0-Article|1-News',
    `views` int(11) NOT NULL DEFAULT 0,
    `pub_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM {TABLEENCODING} AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure `{PREFIX}s_post_translates`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}s_post_translates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `post` int(11) NOT NULL,
    `lang` varchar(16) NOT NULL,
    `pagetitle` varchar(512) NOT NULL,
    `introtext` text NOT NULL,
    `content` text NOT NULL,
    `epilog` text NOT NULL,
    `seotitle` varchar(128) NOT NULL,
    `seodescription` varchar(255) NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `post_lang` (`post`,`lang`)
) ENGINE=MyISAM {TABLEENCODING} AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure `{PREFIX}s_post_content_tag`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}s_post_content_tag` (
    `tag_id` int(11) NOT NULL,
    `post_id` int(11) NOT NULL
) ENGINE=MyISAM {TABLEENCODING};

-- --------------------------------------------------------

--
-- Table structure `{PREFIX}s_post_tags`
--

CREATE TABLE IF NOT EXISTS `{PREFIX}s_post_tags` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `alias` varchar(128) NOT NULL,
    `base` varchar(255) NOT NULL,
    `base_content` text NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM {TABLEENCODING} AUTO_INCREMENT=1;

-- --------------------------------------------------------