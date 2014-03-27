<?php
    // Prevent running directly:
    if (!defined('PERCH_DB_PREFIX')) exit;


    $API = new PerchAPI(1.0, 'perch_blog');

    $Settings = $API->get('Settings');

    if ($Settings->get('perch_blog_update')->val()!='3.7.5') {

        $UserPrivileges = $API->get('UserPrivileges');
        $UserPrivileges->create_privilege('perch_blog', 'Access the blog');
        $UserPrivileges->create_privilege('perch_blog.post.create', 'Create posts');
        $UserPrivileges->create_privilege('perch_blog.post.delete', 'Delete posts');
        $UserPrivileges->create_privilege('perch_blog.post.publish', 'Publish posts');
        $UserPrivileges->create_privilege('perch_blog.comments.moderate', 'Moderate comments');
        $UserPrivileges->create_privilege('perch_blog.comments.enable', 'Enable comments on a post');
        $UserPrivileges->create_privilege('perch_blog.categories.manage', 'Manage categories');
        $UserPrivileges->create_privilege('perch_blog.import', 'Import data');
        $UserPrivileges->create_privilege('perch_blog.authors.manage', 'Manage authors');

        
        $db = $API->get('DB');
        

        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_posts` ADD `postImportID` VARCHAR(64)  NULL  DEFAULT NULL  AFTER `postCommentCount`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_posts` ADD `postCommentCount` INT(10)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `authorID`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_posts` ADD `postLegacyURL` VARCHAR(255)  NULL  DEFAULT NULL  AFTER `postImportID`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_posts` ADD `postAllowComments` TINYINT(1)  UNSIGNED  NOT NULL  DEFAULT '1'  AFTER `postLegacyURL`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_authors` ADD `authorImportRef` VARCHAR(64)  NULL  DEFAULT NULL  AFTER `authorSlug`";
        $db->execute($sql);
        $sql = "INSERT INTO `".PERCH_DB_PREFIX."settings` (`settingID`, `userID`, `settingValue`) VALUES ('perch_blog_post_url', 0, '/blog/post.php?s={postSlug}')";
        $db->execute($sql);

        // 3.7
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_categories` ADD `categoryPostCount` INT(10)  UNSIGNED  NOT NULL  DEFAULT '0'  AFTER `categorySlug`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_categories` ADD `categoryDynamicFields` TEXT  NULL  AFTER `categoryPostCount`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_posts` ADD `postTemplate` VARCHAR(255)  NOT NULL  DEFAULT 'post.html'  AFTER `postAllowComments`";
        $db->execute($sql);
        $sql = "ALTER TABLE `".PERCH_DB_PREFIX."blog_authors` ADD `authorDynamicFields` TEXT  NULL  AFTER `authorImportRef`";
        $db->execute($sql);

        $Cats = new PerchBlog_Categories($API);
        $Cats->update_post_counts();

        $Settings->set('perch_blog_update', '3.7.5');

    }

?>