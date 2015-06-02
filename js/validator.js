
function validate (validationFunction, value) {
    if (validationFunction(value)) {
        return value;
    }
    return null;
}

function validateName (name) {
    return validate(isValidName, name);
}

function validateEmail (email) {
    return validate(isValidEmail, email);
}