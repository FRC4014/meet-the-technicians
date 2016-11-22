/* javascript for back-end */
function formSubmit(id) {
    if(confirm('Are you sure? Deletion is permenant. You will also lose unsaved changes.')){
        document.getElementById("mt_delete").value = id;
        document.getElementById("mt_form").submit();
        }
    }
function revealNewPerson (){
    document.getElementById('person_new').style.display = 'inline-block';
    document.getElementById('addnew').style.display = 'none';
    }
    
jQuery('document').ready(function(){
    jQuery('.button.upload-image').click(function(){
        jQuery(this).addClass('uploading'); //add to allow change to the pic and img
    });
});

function send_to_editor(input){ //dummy function to fulfill  wordpress api
    }

window.addEventListener("DOMContentLoaded", redirectPage, false);
window.addEventListener("DOMContentLoaded", registerHelper, false);

function registerHelper(){
    var pageInput = document.getElementById('MTfeaturename');
    if (pageInput !== null){
        console.log('event listener registered');
        pageInput.addEventListener("input", tableSuffixHelper, false);
        }
    }
function redirectPage(){
    if (document.getElementById('title') === null){
        return;
        }
    else if (document.getElementById('title').value === pageName) {
        window.location = "edit.php?post_type=page&page=" + redirectName;
        }
    }
    
function tableSuffixHelper(){
    var pageInput = document.getElementById('MTfeaturename');
    var suffixInput = document.getElementById('MTtablesuffix');
    var parsedPage = pageInput.value.toLowerCase().replace(/\s/g, '').replace(/the/i, '');
    suffixInput.value = parsedPage;
    console.log('name changed');
    }