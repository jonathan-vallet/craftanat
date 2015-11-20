function readURL(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
        	var previewImage = document.createElement('img');
        	previewImage.src = e.target.result;
        	var previousImage = input.parentNode.querySelector('img');
        	if(previousImage !== null)
        		previousImage.remove();
        	input.parentNode.appendChild(previewImage);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

[].forEach.call(document.querySelectorAll('input[type="file"]'), function(el) {
	el.addEventListener('change', function(){
    	readURL(this);
 	});
});
