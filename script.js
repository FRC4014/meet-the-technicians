/* javascript for both front-end and back-end */
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

window.addEventListener("DOMContentLoaded", redirectPage, false);

function redirectPage(){
    if (document.getElementById('title') === null){
        return;
        }
    else if (document.getElementById('title').value === pageName) {
        window.location = "edit.php?post_type=page&page=" + redirectName;
        }
    }