/*SCRIPTS FOR MESSAGES.HTML ######################################################################################*/
function loadFunction()
{
    document.getElementById("allButton").style.background='grey';
    document.getElementById("messageWindow").style.display='none';
    return;
}

function allMessagesClicked()
{
    document.getElementById("message1").style.display='flex';
    document.getElementById("message2").style.display='flex';
    document.getElementById("message3").style.display='flex';
    document.getElementById("message4").style.display='flex';
    document.getElementById("allButton").style.background='grey';
    document.getElementById("unreadButton").style.background='linear-gradient(150deg, #451ded, #8e0ce3)';
    return;
}

function unreadMessagesClicked()
{
    document.getElementById("message2").style.display='none';
    document.getElementById("message3").style.display='none';
    document.getElementById("unreadButton").style.background='grey';
    document.getElementById("allButton").style.background='linear-gradient(150deg, #451ded, #8e0ce3)';
    return;
}

function openMessage(message)
{
    if(message.id == "message1")
    {
        document.getElementById('messageHeaderUsername').innerText = 'MSimpson42';
    }
    else if (message.id == "message2")
    {
        document.getElementById('messageHeaderUsername').innerText = 'barry_white2023';
    }
    else if (message.id == "message3")
    {
        document.getElementById('messageHeaderUsername').innerText = 'LDavid_1996';
    }
    else if (message.id == "message4")
    {
        document.getElementById('messageHeaderUsername').innerText = 'SusanCartwright';
    }
    document.getElementById("messagesDiv").style.display='none';
    document.getElementById("messageWindow").style.display='block';
    document.getElementById("selectorButtonsDiv").style.display='none';
}

function closeMessage()
{
    document.getElementById("messagesDiv").style.display='block';
    document.getElementById("messageWindow").style.display='none';
    document.getElementById("selectorButtonsDiv").style.display='block';
}

function sendMessage()
{
    var messagesWindow = document.getElementById("messages");
    var newMessageContainer = document.createElement("div");
    var messageText = document.getElementById("messageInputBox").value;
    var messageTextContainer = document.createElement("p");
    var messageTime = document.createElement("p")
    var d = new Date();

    messageTime.classList.add("messageTimeSentUser");
    messageTime.innerText = d.getHours() + ":" + d.getMinutes();
    messageTextContainer.classList.add("messageContent")
    messageTextContainer.innerText = messageText;
    newMessageContainer.classList.add("messageContainerUser");
    newMessageContainer.appendChild(messageTextContainer);

    messagesWindow.appendChild(newMessageContainer);
    messagesWindow.appendChild(messageTime);

    messagesWindow.scrollTop = messagesWindow.scrollHeight;

    document.getElementById("messageInputBox").value = "";
}

/*SCRIPTS FOR PROFILE.HTML ################################################*/
function editProfileButton()    /*to be implemented later.*/
{

}

