var questionCategorySelect = document.getElementById('questionCategorySelect');
var questionElementList =  document.querySelectorAll('.question');
if(questionCategorySelect) {
	questionCategorySelect.addEventListener('change', function() {
		[].forEach.call(questionElementList, function(el) {
		    el.style.display = 'none';
		});
		
		if(this.value) {
			document.getElementById('questionCategory_' + this.value).style.display = 'block';
		}
	}, false);
}

var holeTextarea = document.querySelector('#questionCategory_2 textarea');
if(holeTextarea) {
	holeTextarea.addEventListener('blur', checkWord, false);
	holeTextarea.addEventListener('keyup', checkWord, false);
}

var holeTextRegex = /<champ([0-9]+)>/g;

function checkWord() {
	var matches, fieldList = [];
	while (matches = holeTextRegex.exec(this.value)) {
	    fieldList.push(matches[1]);
	}

	for (var index in fieldList) {
		var answerNumber = fieldList[index];
		var answerElement = document.getElementById('holeTextAnswer_' + answerNumber);
		if(answerElement === null) {
			var anwserElement = document.createElement('input');
			anwserElement.type = 'text';
			anwserElement.id = 'holeTextAnswer_' + answerNumber;
			anwserElement.name = 'answer' + answerNumber;
			anwserElement.classList.add('form-control');
			anwserElement.placeholder= 'Valeur du champ ' + answerNumber;
			
			var pElement = document.createElement('p');
			pElement.classList.add('form-group');
			pElement.appendChild(anwserElement);
			
			document.getElementById('questionCategory_2').insertBefore(pElement, document.getElementById('holeTextSubmit'));
		}
	}
}

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
        	
        	var graphicImage = document.querySelector('#graphicImageContainer img')
			if(graphicImage)
				graphicImage.addEventListener('click', setGraphicAnswer, false);
        	var schemaImage = document.querySelector('#schemaImageContainer img')
			if(schemaImage) {
				schemaImage.addEventListener('click', setSchemaAnswer, false);
				schemaImage.addEventListener('contextmenu', setSchemaAnswer, false);
			}
        };

        reader.readAsDataURL(input.files[0]);
    }
}

[].forEach.call(document.querySelectorAll('input[type="file"]'), function(el) {
	el.addEventListener('change', function(){
    	readURL(this);
 	});
});

function setGraphicAnswer(e) {
	var x = e.offsetX;
	var y = e.offsetY;
	var defaultRadius = 5;
	
	var answer = document.createElement('span');
	var answerNumber = document.querySelectorAll('.graphicAnswer').length + 1;
	var $graphicContainer = document.getElementById('graphicImageContainer');
	answer.classList.add('graphicAnswer');
	answer.setAttribute('data-answer', answerNumber);
	answer.id = 'graphicAnswer' + answerNumber;
	answer.style.width = (2 * defaultRadius) + 'px';
	answer.style.height = (2 * defaultRadius) + 'px';
	answer.style.left = (x - defaultRadius) + 'px';
	answer.style.top = (y + 47 - defaultRadius) + 'px'; // 22: offse due to label
	answer.addEventListener('click', function(e) {
		var selectedAnswer = document.querySelector('.graphicAnswer.selected');
		if(selectedAnswer) 
			selectedAnswer.classList.remove('selected');
		e.target.classList.add('selected');
		document.querySelector('input[name="answerRadius' + e.target.getAttribute('data-answer') + '"]').focus();
	}, false);
	$graphicContainer.appendChild(answer);
	
	var answerInput = document.createElement('input');
	answerInput.type = 'hidden';
	answerInput.name = 'answer' + answerNumber;
	answerInput.value = x + '/' + y;
	console.log(answerInput);
	$graphicContainer.appendChild(answerInput);
	
	var answerRadius = document.createElement('input');
	answerRadius.type = 'text';
	answerRadius.classList.add('form-control');
	answerRadius.name = 'answerRadius' + answerNumber;
	answerRadius.setAttribute('data-answer', answerNumber);
	answerRadius.value = 5;
	answerRadius.addEventListener('focus', setSelectedAnswer, false);
	answerRadius.addEventListener('blur', updateAnswerRadius, false);
	$graphicContainer.appendChild(answerRadius);
}

var currentRadius;
function setSelectedAnswer(e) {
	var answerNumber = e.target.getAttribute('data-answer');
	document.getElementById('graphicAnswer' + answerNumber).classList.add('selected');
	currentRadius = parseInt(e.target.value);
}

function updateAnswerRadius(e) {
	var answerNumber = e.target.getAttribute('data-answer');
	var answerElement = document.getElementById('graphicAnswer' + answerNumber);
	answerElement.classList.remove('selected');
	var newRadius = parseInt(e.target.value);
	answerElement.style.width = (2 * newRadius) + 'px';
	answerElement.style.height = (2 * newRadius) + 'px';
	console.log(answerElement.style.left);
	answerElement.style.left = (answerElement.offsetLeft + currentRadius - newRadius) + 'px';
	answerElement.style.top = (answerElement.offsetTop + currentRadius - newRadius) + 'px';
}

document.body.addEventListener('keydown', function(e) {
	if(e.which === 46) {
		var selectedAnswer = document.querySelector('.graphicAnswer.selected');
		if(selectedAnswer) {
			var questionNumber = selectedAnswer.getAttribute('data-answer');
			selectedAnswer.remove();
			document.querySelector('input[name="answerRadius' + questionNumber +'"]').remove();
			document.querySelector('input[name="answer' + questionNumber +'"]').remove();
		}
	}
}, false);

var formList = document.querySelectorAll('form');
[].forEach.call(formList, function(el) {
    el.addEventListener('submit', function(e) {
    	var form = e.target;
    	var structureSelect = form.querySelector('select[name="structure"]');
		if(!structureSelect.value) {
			structureSelect.classList.add('error');
	    	e.preventDefault();
	    	return false;
		}
    }, false);
});

// Schema
function setSchemaAnswer(e) {
	// Updates parent width to avoir bug
	document.getElementById('schemaImageContainer').style.width = this.width + 200 + 'px';
	
	var x = e.offsetX;
	var y = e.offsetY;
	e.preventDefault();
	var answerBar = document.createElement('span');
	var answerNumber = document.querySelectorAll('.schemaAnswerBar').length + 1;
	var $schemaContainer = document.getElementById('schemaImageContainer');
	answerBar.classList.add('schemaAnswerBar');
	answerBar.id = 'graphicAnswerBar' + answerNumber;
	if(e.type === 'contextmenu') {
		answerBar.style.width = (this.width + 50 - x) + 'px';
		answerBar.style.right = '-50px';
	} else {
		answerBar.style.width = (x + 50) + 'px';
		answerBar.style.left = '-50px';
	}
	answerBar.setAttribute('data-answer', answerNumber);
	answerBar.style.top = (y + 47) + 'px'; 
	
	$schemaContainer.appendChild(answerBar);

	var answerDataInput = document.createElement('input');
	answerDataInput.type = 'hidden';
	answerDataInput.name = 'answerData' + answerNumber;
	answerDataInput.setAttribute('data-answer', answerNumber);
	answerDataInput.value = x + '/' + y + '/' + (e.type === 'contextmenu' ? 2 : 1);
	$schemaContainer.appendChild(answerDataInput);
	
	var answer = document.createElement('input');
	answer.type = 'text';
	answer.classList.add('form-control');
	answer.name = 'answer' + answerNumber;
	answer.setAttribute('data-answer', answerNumber);
	answer.style.width = '200px';
	answer.style.top = (y + 47) + 'px'; 
	if(e.type === 'contextmenu') {
		answer.style.right = '-250px';
	} else {
		answer.style.left = '-50px';
	}
	$schemaContainer.appendChild(answer);
}

