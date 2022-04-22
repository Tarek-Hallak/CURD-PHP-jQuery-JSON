$(document).ready(function () {
    //toggle sections
    $(".cardHeader").click(function () {
        const this_head = $(this);
        const this_content = $(this).parent().find('.cardContent');
        this_content.slideToggle('fast');
    });

    countPos = 0;
    countEdu = 0;

    //add new education card
    $("#addEdu").click(function () {
        event.preventDefault();
        countEdu++;
        const source = $("#eduTemplate").html();
        $('#eduContent').append(source.replace(/@COUNT@/g, countEdu));

        //add event listener to complete schools
        $('.schools').autocomplete({
            source: "school.php"
        });
    });

    //add new position card
    $("#addPos").click(function () {
        event.preventDefault();
        countPos++;
        const source = $("#posTemplate").html();
        $('#posContent').append(source.replace(/@COUNT@/g, countPos));
    });
});

//---Validators
function loginValidate() {
    console.log('login Validating...');
    try {
        const em = document.getElementById('id_1722').value;
        const pw = document.getElementById('id_1723').value;
        console.log("Validating pass= " + pw);
        if (pw === null || pw === '' || em == null || em === '') {
            alert("Both fields must be filled out");
            return false;
        }
        return true;
    } catch (e) {
        console.log("Login Error: " + e);
        return false;
    }
}

function addValidate() {
    console.log('Validating Profile...');
    try {
        const fn = document.getElementById('id_1').value;
        const ln = document.getElementById('id_2').value;
        const em = document.getElementById('id_3').value;
        const he = document.getElementById('id_4').value;
        const su = document.getElementById('id_5').value;
        if (fn === '' || ln === '' || em === '' || he === '' || su === '') {
            alert("All fields must be filled out");
            return false;
        }
        if (!em.match(/\S+@\S+\.\S+/)) {
            alert("invalid email address example: test@something.com");
            return false;
        }
        console.log('Validating Profile success!');

        //Eduction Validating
        console.log('Validating Eduction...');
        for (let i = 0; i < 10; i++) {
            const year = document.getElementById("year-" + i);
            const school = document.getElementById("school-" + i);
            if (!year || !school) continue; //if there is no element stop so we dont have an exception
            if (year.value === '' || school.value === '') {
                alert("All Eduction fields must be filled out");
                return false;
            }
            if (isNaN(year.value)) {
                alert("year must be a number!");
                return false;
            }
        }
        console.log('Validating Eduction success!');

        //Position Validating
        console.log('Validating Position...');
        for (let i = 0; i < 10; i++) {
            const date = document.getElementById("date-" + i);
            const desc = document.getElementById("des-" + i);
            if (!date || !desc) continue; //if there is no element stop so we dont have an exception
            if (date.value === '' || desc.value === '') {
                alert("All Position fields must be filled out");
                return false;
            }
            if (isNaN(date.value)) {
                alert("date must be a number!");
                return false;
            }
        }
        console.log('Validating Position success!');
        return true;
    } catch (e) {
        console.log(e);
        return false;
    }
}