<?php
    
    $Blog = new PerchBlog_Posts($API);
    $message = false;
    $Categories = new PerchBlog_Categories($API);
    $categories = $Categories->all();

    $Authors = new PerchBlog_Authors;
    $Author = $Authors->find_or_create($CurrentUser);

    $HTML = $API->get('HTML');

    if (!$CurrentUser->has_priv('perch_blog.post.create')) {
        PerchUtil::redirect($API->app_path());
    }

    if (isset($_GET['id']) && $_GET['id']!='') {
        $postID = (int) $_GET['id'];    
        $Post = $Blog->find($postID, true);
        $details = $Post->to_array();
        
        $template = $Post->postTemplate();
            
    }else{
        $Post = false;
        $postID = false;
        $details = array();

        if (!$CurrentUser->has_priv('perch_blog.post.create')) {
            PerchUtil::redirect($API->app_path());
        }

        $template = false;
       
    }


    if (!$template) {
        $template = 'post.html';
    }

    $Template   = $API->get('Template');
    $Template->set('blog/'.$template, 'blog');
    $tags = $Template->find_all_tags();


    $result = false;

    $Form = $API->get('Form');
    $Form->require_field('postDateTime_minute', 'Required');


    $TitleTag = $Template->find_tag('postTitle');
    if ($TitleTag) {
        if ($TitleTag->required()) {
            $Form->require_field('postTitle', 'Required');
        }
    }
    
    $DescTag = $Template->find_tag('postDescHTML');
    if ($DescTag) {
        if ($DescTag->required()) {
            $Form->require_field('postDescRaw', 'Required');
        }
    }
    
    $Form->set_required_fields_from_template($Template, array('postDescHTML', 'postTitle'));

    if ($Form->submitted()) {
    	        
        $postvars = array('postID','postTitle','postDescRaw','cat_ids','postTags','postStatus', 'postAllowComments', 'postTemplate', 'authorID');
		
    	$data = $Form->receive($postvars);
    	
    	$data['postDateTime'] = $Form->get_date('postDateTime');

        if (!isset($data['postAllowComments'])) {
            $data['postAllowComments']  = '0';
        }


        $prev = false;

        if (isset($details['postDynamicFields'])) {
            $prev = PerchUtil::json_safe_decode($details['postDynamicFields'], true);
        }
    	
    	$dynamic_fields = $Form->receive_from_template_fields($Template, $prev);
    	$data['postDynamicFields'] = PerchUtil::json_safe_encode($dynamic_fields);
    	

        if (!$CurrentUser->has_priv('perch_blog.post.publish')) {
            $data['postStatus'] = 'Draft';
        }


    	if (is_object($Post)) {

            if (!isset($data['postTitle']) || $data['postTitle']=='') {
                $data['postTitle'] = 'Post '.$Post->id();
            }            



    	    $Post->Template = $Template;
    	    $result = $Post->update($data);
    	}else{

    	    if (isset($data['postID'])) unset($data['postID']);

            if (!$CurrentUser->has_priv('perch_blog.comments.enable')) {
                $data['postAllowComments']  = '0';
            }


    	    $new_post = $Blog->create($data);
    	    if ($new_post) {

                if (!isset($data['postTitle']) || $data['postTitle']=='') {
                    $data['postTitle'] = 'Post '.$new_post->id();
                }   

                $new_post->update($data);
    	        $result = true;

                PerchBlog_Cache::expire_all();
                $Categories->update_post_counts();

    	        PerchUtil::redirect($API->app_path() .'/edit/?id='.$new_post->id().'&created=1');
    	    }else{
    	        $message = $HTML->failure_message('Sorry, that post could not be updated.');
    	    }
    	}
    	
    	
        if ($result) {
            $message = $HTML->success_message('Your post has been successfully updated. Return to %spost listing%s', '<a href="'.$API->app_path() .'">', '</a>');  
        }else{
            $message = $HTML->failure_message('Sorry, that post could not be updated.');
        }
        
        if (is_object($Post)) {
            $details = $Post->to_array();
        }else{
            $details = array();
        }
        
        // clear the caches
        PerchBlog_Cache::expire_all();


        // update category post counts;
        $Categories->update_post_counts();


        // Has the template changed? If so, need to redirect back to kick things off again.
        if ($data['postTemplate'] != $template) {
            PerchUtil::redirect($API->app_path() .'/edit/?id='.$Post->id().'&edited=1');
        }

    }
    
    if (isset($_GET['created']) && !$message) {
        $message = $HTML->success_message('Your post has been successfully created. Return to %spost listing%s', '<a href="'.$API->app_path() .'">', '</a>'); 
    }

    if (isset($_GET['edited']) && !$message) {
        $message = $HTML->success_message('Your post has been successfully updated. Return to %spost listing%s', '<a href="'.$API->app_path() .'">', '</a>');  
    }


    // is it a draft?
    if (is_object($Post) && $Post->postStatus()=='Draft') {
        $draft = true;

        $url  = PerchUtil::html($Post->postURL());
        if (strpos($url, '?')!==false) {
            $url .= '&amp;'.PERCH_PREVIEW_ARG.'=all';
        }else{
            $url = rtrim($url,'/').'/'.PERCH_PREVIEW_ARG;
        }
        
        $message = $Lang->get('%sYou are editing a draft. %sPreview%s', '<p class="alert draft">', '<a href="'.$url.'" class="action draft-preview">', '</a></p>');
               
        
    }else{
        $draft = false;
    }
    


    $post_templates = PerchUtil::get_dir_contents(PerchUtil::file_path(PERCH_TEMPLATE_PATH.'/blog/posts'), false);

?>
