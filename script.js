/* javascript for both front-end and back-end */
function formSubmit(id) {
    if(confirm('Are you sure? Deletion is permenant. You will also lose unsaved changes.')){
        document.getElementById("mt_delete").value = id;
        document.getElementById("mt_form").submit();
        }
    }