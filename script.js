/* javascript for both front-end and back-end */

function refreshpic(event) {
    var pic = document.getElementById('mt_1_img');
    var input = document.getElementById('mt_1_pic');
    pic.src = input.value;
    };