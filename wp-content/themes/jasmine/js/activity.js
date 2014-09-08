
jQuery(document).ready(function($) {

    if($("#activity-stream").length !=0){
    $("#activity-stream").html("loading.............");
         $.get(SITEURL+"/api/activities/me/", {}, function(collection)  {  

                  display_activitites(collection);
            });
    }

    
	$(document).on("change", "#filter-by-component", function(e) {
        $("#activity-stream").html("loading.............");
      
        $.get(SITEURL+"/api/activities/me/?component="+$(e.target).val(), {}, function(collection)  {  

                  display_activitites(collection);
            });

    });
    $(document).on("change", "#filter-by-type", function(e) {
        $("#activity-stream").html("loading.............");
      
        $.get(SITEURL+"/api/activities/me/?filter[action]="+$(e.target).val(), {}, function(collection)  {  

                  display_activitites(collection);
            });

    });  
	  $(document).on("click", ".ac_form_submit", function(e) {

            if($(e.target).attr('activity-type')=="activity_comment"){
                parent_id = $(e.target).attr('activity-id')
                activity_id  = $(e.target).attr('activity-item-id')
            }else{
                parent_id = 0
                activity_id  = $(e.target).attr('activity-id')             
            }
	  		 $.post("http://localhost/jasmine/api/comment/create", { 
                parent_id:  parent_id, 
                activity_id:  activity_id, 
                content:  $(e.target).prev().find(".comment").val(),  
                 
              }, function(response)  {  

            	 console.log(response)
        	});
	  });
      $(document).on("click", "#aw-whats-new-submit", function(e) {

          console.log("#aw-whats-new-submit")
             $.post("http://localhost/jasmine/api/activity/create", { 
               action:  '@admin posted an update', 
               content:  $("#whats-new").val(),  
               component: 'activity',  
               type: 'activity_update',  
                 
              }, function(response)  {  

                 console.log(response)
            });
      });

      function display_activitites(collection){
        $("#activity-stream").html("");
        _.each(collection, function(item,itemValue){
            $("#activity-stream").append(activity_entry(item));
            
        });
        
      }

      function activity_entry(item){
      
        activity = "";
        if(item.id !=undefined){
            activity = '<li   id="activity-'+item.id+'">';
            activity +='<div class="activity-content">';
            activity +='<div class="activity-header">';
            activity +='<b>Action: </b>'+item.action; 
            activity +='</div>';
            if(item.content !=""){
                activity +='<div class="activity-inner">';
                activity +='<b>Content: </b>'+item.content; 
                activity +='</div>';
            }
            activity +='<div class="activity-meta">';
            activity +='<a href="javascript:void(0)" class="acomment-reply bp-primary-action reply-to-comment" id="acomment-comment-'+item.id+'"  activity-type="'+item.type+'" activity-id="'+item.id+'" item-id="'+item.item_id+'">Comment</a>'
            activity +='</div>';
            activity +='<div class="activity-comments">';
            activity +='<ul  class="activity-stream">';
             
            if(item.children.length !=0){  
                _.each( item.children , function(childItem,childItemValue){

                    activity += activity_entry(childItem) ;
                    
                });
             
            }
        activity +='</ul>';

        activity +='<div id="activity-comment-reply-'+item.id+'" >';

        activity +='</div>';

        activity +='</div>';

        activity +='</div>';

        activity +='</li>';

        }
        
        return activity;

      }

     


      function activityCommentUI(activityId,activityType,activityItemId){

        comment = '<div class="ac-reply-content">'

        comment +='<div class="ac-textarea">'

        comment +='<textarea  class="comment"></textarea>'

        comment +='</div>'

        comment +='<input type="button" class="ac_form_submit" value="Post" activity-id='+activityId+' activity-item-id='+activityItemId+' activity-type='+activityType+'> &nbsp; <a href="javascript:void(0)" class="ac-reply-cancel" comment-parent='+activityId+'>Cancel</a>'
       
        comment +='</div>'

        return comment;
      }
 

       $(document).on("click", ".reply-to-comment", function(e) {

             $("#activity-comment-reply-"+$(e.target).attr('activity-id')).html(activityCommentUI($(e.target).attr('activity-id'),$(e.target).attr('activity-type'),$(e.target).attr('item-id')))

       })

       $(document).on("click", ".ac-reply-cancel", function(e) { 

            $("#activity-comment-reply-"+$(e.target).attr('comment-parent')).children().remove()


       })

      
});
