function checkEmail() {
var email = document.getElementById('email');
var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
if (!filter.test(email.value)) {
alert('Please enter a valid email address');
email.focus();
return false;
}
}

function preview_q(appendTopic) {
	document.forms['searchForm'].q.style.color='#666';
	document.forms['searchForm'].q.value=perm_query+' '+appendTopic;
}
function reset_q() {
	document.forms['searchForm'].q.style.color='#000';
	document.forms['searchForm'].q.value=perm_query;
}



