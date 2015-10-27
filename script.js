/* javascript for both front-end and back-end */
function formSubmit(id)
{
  //confirm('Are you sure?');
  document.getElementById("mt_delete").value = id;
  document.getElementById("mt_form").submit();
  
}