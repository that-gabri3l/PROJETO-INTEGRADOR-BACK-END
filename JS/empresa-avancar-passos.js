'use strict'

let currentStep = 1;
const totalSteps = 3;

function changeStep(direction) {
    document.getElementById(`step-${currentStep}`).classList.remove('active');

    if (direction === 1) {
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-active');
        document.getElementById(`indicator-${currentStep}`).classList.add('step-done');
        document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = '✓';
        if (currentStep < totalSteps) document.getElementById(`line-${currentStep}`).classList.add('step-done-line');
    } else {
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-active');
        document.getElementById(`indicator-${currentStep}`).classList.remove('step-done');
        document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = currentStep;
        if (currentStep > 1) document.getElementById(`line-${currentStep - 1}`).classList.remove('step-done-line');
    }

    currentStep += direction;

    document.getElementById(`step-${currentStep}`).classList.add('active');

    document.getElementById(`indicator-${currentStep}`).classList.remove('step-done');
    document.getElementById(`indicator-${currentStep}`).classList.add('step-active');
    document.getElementById(`indicator-${currentStep}`).querySelector('.step-circle').innerHTML = currentStep;

    document.getElementById('btnPrev').style.display = (currentStep === 1) ? 'none' : 'block';

    if (currentStep === totalSteps) {
        document.getElementById('btnNext').style.display = 'none';
        document.getElementById('btnSubmit').style.display = 'block';
    } else {
        document.getElementById('btnNext').style.display = 'block';
        document.getElementById('btnSubmit').style.display = 'none';
    }
}