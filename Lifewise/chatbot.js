function sendMessage() {
    const inputField = document.getElementById("user-input");
    const userText = inputField.value.trim();
    if (userText === "") return;
  
    displayMessage(userText, "user");
    inputField.value = "";
  
    setTimeout(() => {
      const response = getBotResponse(userText.toLowerCase());
      displayMessage(response, "bot");
    }, 500);
  }
  
  function displayMessage(message, sender) {
    const chatBody = document.getElementById("chat-body");
    const messageDiv = document.createElement("div");
    messageDiv.classList.add(sender === "user" ? "user-message" : "bot-message");
    messageDiv.textContent = message;
    chatBody.appendChild(messageDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
  }
  
  function toggleChatbot() {
    const window = document.getElementById("chatbot-window");
    window.style.display = window.style.display === "none" ? "flex" : "none";
  }  
function sendMessage() {
    const inputField = document.getElementById("user-input");
    const userText = inputField.value.trim();
    if (userText === "") return;
  
    displayMessage(userText, "user");
    inputField.value = "";
  
    setTimeout(() => {
      const response = getBotResponse(userText.toLowerCase());
      displayMessage(response, "bot");
    }, 500);
  }
  
  function displayMessage(message, sender) {
    const chatBody = document.getElementById("chat-body");
    const messageDiv = document.createElement("div");
    messageDiv.classList.add(sender === "user" ? "user-message" : "bot-message");
    messageDiv.textContent = message;
    chatBody.appendChild(messageDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
  }
  
  function getBotResponse(input) {
    // Normalize input
    input = input.toLowerCase();
  
    // Greetings
    if (["hi", "hello", "hey", "good morning", "good afternoon", "good evening"].some(greet => input.includes(greet))) {
      return "Hello! 👋 I'm your LifeWise Assistant. How can I help you today?";
    } else if (input.includes("how are you")) {
      return "I'm great! 😊 Thanks for asking. Ready to help you with health questions.";
    
    // Health topics
    } else if (input.includes("hiv")) {
      return "HIV is a virus that attacks the immune system. It can be managed with medication called ARVs. Get tested and know your status.";
    } else if (input.includes("pregnancy") || input.includes("early pregnancy")) {
      return "Prevent early pregnancy by using contraceptives, abstaining, or practicing safe sex. Talk to a health provider for advice.";
    } else if (input.includes("tested") || input.includes("clinic")) {
      return "Visit your nearest youth-friendly clinic or hospital to get tested for HIV, STIs, or pregnancy.";
    } else if (input.includes("condom")) {
      return "Condoms are a reliable method to prevent STIs and pregnancy. Use one every time you have sex.";
    } else if (input.includes("contraceptive") || input.includes("birth control")) {
      return "Contraceptives include pills, injectables, implants, IUDs, and condoms. A nurse can help you choose what suits you best.";
    } else if (input.includes("sti") || input.includes("infection")) {
      return "STIs are sexually transmitted infections like chlamydia or gonorrhea. Use protection and get tested regularly.";
    } else if (input.includes("abstinence")) {
      return "Abstinence is the only 100% effective method of preventing pregnancy and STIs.";
    } else if (input.includes("peer pressure")) {
      return "Peer pressure is real. Always make choices that protect your health and future. Say no when you need to!";
    } else if (input.includes("life skills")) {
      return "Life skills like decision-making, communication, and self-awareness help you avoid risky behavior.";
    } else if (input.includes("help") || input.includes("faq") || input.includes("question")) {
      return "You can ask about HIV, STIs, contraceptives, testing, pregnancy, condoms, or life skills!";
    
    // Default fallback
    } else {
      return "I'm not sure about that yet 🤔. Try asking me about HIV, contraceptives, or pregnancy prevention.";
    }
  }
