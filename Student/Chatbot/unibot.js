document.addEventListener('DOMContentLoaded', () => {
    const chatScrollBox = document.querySelector(".chatbot");
    const chatContainer = document.getElementById("chat-container");
    const userInput = document.getElementById("user-input");
    const sendBtn = document.getElementById("send-btn");
    const timestampEl = document.getElementById("chat-timestamp");

    const dropdownBtn = document.querySelector(".dropdown-btn");
    const dropdownContent = document.querySelector(".dropdown-content");
    const dropdownItems = dropdownContent ? dropdownContent.querySelectorAll("[data-action]") : [];

    const overlay = document.getElementById('reminder-overlay');
    const cancelOverlayBtn = document.getElementById('cancelBtn');
    const yesOverlayBtn = document.getElementById('yesBtn');

    const emptyChat = document.getElementById('empty-chat');
    const startChatBtn = document.getElementById('startChatBtn');

    const snackbar = document.getElementById('snackbar');

    const header = document.querySelector('.header');
    const inputArea = document.querySelector('.input-area');

    // time
    function formatAMPM(date) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        const day = date.getDate();
        const month = date.toLocaleString('default', { month: 'short' });
        return `${day} ${month}, ${hours}:${minutes} ${ampm}`;
    }
    timestampEl && (timestampEl.innerText = formatAMPM(new Date()));

    // default message
    function defaultMessage() {
        return `
         <div class="bot-message">
            <div class="chat-author">Unibot</div>
            <div class="chat-message-wrapper">
                <div class="chat-avatar">
                    <img src="../../image/icons/avatar.png" alt="Unibot Avatar" class="avatar-icon">
                </div>
                <div class="chat-message-content">
                    <div class="chat-bubble">
                        Hello there 👋! I'm Unibot, your student wellness companion. How can I help you today?
                    </div>
                </div>
            </div>
        </div>`;
    }

    function escapeHtml(str) {
        return str.replace(/[&<>"']/g, (m) =>
            ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
        );
    }

    // save chat
    function saveChat() {
        const clone = chatContainer.cloneNode(true);
        const typing = clone.querySelector(".bot-message.bot-typing");
        if (typing) typing.remove();
        localStorage.setItem(chatKey, clone.innerHTML);
    }

    const savedChat = localStorage.getItem(chatKey);
    if (savedChat) {
        chatContainer.innerHTML = savedChat;
    } else {
        chatContainer.innerHTML = defaultMessage();
        saveChat();
    }

    if (emptyChat) emptyChat.style.display = 'none';

    function scrollToBottom() {
        setTimeout(() => {
            chatScrollBox.scrollTop = chatScrollBox.scrollHeight;
        }, 50);
    }
    
    function toggleSendButton() {
        const message = userInput.value.trim();
        sendBtn.disabled = message === "";
    }

    toggleSendButton(); 

    userInput.addEventListener("input", toggleSendButton);
   
    // user message
    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        const userHTML = `
        <div class="user-message latest-user-msg">
            <div class="message-wrapper">
                <div class="chat-bubble">${escapeHtml(message)}</div>
            </div>
        </div>`;
        chatContainer.insertAdjacentHTML("beforeend", userHTML);
        chatContainer.style.display = 'none';
        chatContainer.offsetHeight; 
        chatContainer.style.display = 'flex';
        scrollToBottom();
        saveChat();    
        userInput.value = "";
        toggleSendButton();

        // typing
        const botTypingHTML = `
        <div class="bot-message bot-typing">
            <div class="chat-author">Unibot</div>
            <div class="chat-message-wrapper">
                <div class="chat-avatar">
                    <img src="../../image/icons/avatar.png" alt="Unibot Avatar" class="avatar-icon">
                </div>
                <div class="chat-bubble typing-bubble">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>`;
        chatContainer.insertAdjacentHTML("beforeend", botTypingHTML);
        scrollToBottom();

        try {
            const res = await fetch('https://chatbot-cifs.onrender.com/chat-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            const data = await res.json();
            const typingEl = chatContainer.querySelector(".bot-message.bot-typing");
            if (typingEl) typingEl.remove();

            const text = data.choices?.[0]?.message?.content || data.reply || "Sorry, I am currently offline.";
            addBotMessage(text);
        } catch (err) {
            const typingEl = chatContainer.querySelector(".bot-message.bot-typing");
            if (typingEl) typingEl.remove();
            addBotMessage("Sorry, I couldn't fetch a reply.");
        }
    }

    sendBtn && sendBtn.addEventListener("click", sendMessage);
    userInput && userInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") sendMessage();
    });

    // unibot reply
    function addBotMessage(content) {
        chatContainer.insertAdjacentHTML("beforeend", `
        <div class="bot-message">
            <div class="chat-author">Unibot</div>
            <div class="chat-message-wrapper">
                <div class="chat-avatar">
                    <img src="../../image/icons/avatar.png" alt="Unibot Avatar" class="avatar-icon">
                </div>
                <div class="chat-message-content">
                    <div class="chat-bubble">${content}</div>
                </div>
            </div>
        </div>`);
        scrollToBottom();
        saveChat();
    }

    // dropdown
    dropdownBtn && dropdownBtn.addEventListener('click', e => {
        e.stopPropagation();
        dropdownContent && dropdownContent.classList.toggle('show');
    });

    dropdownItems.forEach(item => {
        item.addEventListener("click", e => {
            e.stopPropagation();
            const action = item.dataset.action;
            if (action === "clear") {
                overlay && (overlay.style.display = 'flex');
                document.body.classList.add('no-scroll');
                dropdownContent && dropdownContent.classList.remove('show');
            }
            if (action === "search") {
                dropdownContent && dropdownContent.classList.remove("show");
                enterSearchMode();
            }
        });
    });

    document.addEventListener('click', () => {
        if (!overlay || overlay.style.display !== 'flex') {
            dropdownContent && dropdownContent.classList.remove('show');
        }
    });

    // overlay
    cancelOverlayBtn && cancelOverlayBtn.addEventListener('click', () => {
        overlay && (overlay.style.display = 'none');
        document.body.classList.remove('no-scroll');
    });

    yesOverlayBtn && yesOverlayBtn.addEventListener('click', () => {
        overlay && (overlay.style.display = 'none');
        document.body.classList.remove('no-scroll');

        if (snackbar) {
            snackbar.classList.add('success', 'show');
            setTimeout(() => snackbar.classList.remove('show'), 2000);
        }

        if (header) header.style.display = 'none';
        if (timestampEl) timestampEl.style.display = 'none';
        if (inputArea) inputArea.style.display = 'none';
        if (chatContainer) chatContainer.style.display = 'none';
        if (emptyChat) emptyChat.style.display = 'flex';

        localStorage.removeItem(chatKey); 
    });

    // start chat
    startChatBtn && startChatBtn.addEventListener('click', () => {
        if (emptyChat) emptyChat.style.display = 'none';
        if (chatContainer) {
            chatContainer.style.display = 'block';
            chatContainer.innerHTML = defaultMessage();
            saveChat();
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        if (header) header.style.display = '';
        if (timestampEl) {
            timestampEl.style.display = '';
            timestampEl.innerText = formatAMPM(new Date());
        }
        if (inputArea) inputArea.style.display = '';
    });

    // home
    homeBtn && homeBtn.addEventListener('click', () => {
        document.body.style.opacity = '0';
        setTimeout(() => {
            window.location.href = '../dashboard.php';
        }, 100); 
    });

    // search
    function enterSearchMode() {
        const titleEl = header.querySelector(".header-title");
        const backArrow = header.querySelector(".back-arrow");
        const dropdown = header.querySelector(".dropdown");
        if (!titleEl) return;
        if (titleEl.querySelector("#chat-title-input")) return;

        if (backArrow) backArrow.style.display = "none";
        if (dropdown) dropdown.style.display = "none";
        if (inputArea) inputArea.style.display = "none";

        const originalHTML = titleEl.innerHTML;
        const wrapper = document.createElement("div");
        wrapper.classList.add("search-mode");

        const inputWrapper = document.createElement("div");
        inputWrapper.classList.add("search-box");

        const searchIcon = document.createElement("i");
        searchIcon.className = "fas fa-search search-icon";

        const input = document.createElement("input");
        input.type = "text";
        input.id = "chat-title-input";
        input.placeholder = "Search this chat...";

        const clearIcon = document.createElement("i");
        clearIcon.className = "fas fa-times clear-icon";

        const noResult = document.createElement("span");
        noResult.classList.add("no-result");
        noResult.textContent = "No result";
        noResult.style.display = "none";

        const matchCounter = document.createElement("span");
        matchCounter.classList.add("match-counter");
        matchCounter.textContent = "";
        matchCounter.style.display = "none";

        inputWrapper.appendChild(searchIcon);
        inputWrapper.appendChild(input);
        inputWrapper.appendChild(clearIcon);
        inputWrapper.appendChild(noResult);
        inputWrapper.appendChild(matchCounter);

        const navWrapper = document.createElement("div");
        navWrapper.classList.add("search-nav");
        navWrapper.style.display = "flex";
        navWrapper.style.alignItems = "center";
        navWrapper.style.gap = "6px";

        const upArrow = document.createElement("i");
        upArrow.className = "fas fa-arrow-up nav-arrow";
        const downArrow = document.createElement("i");
        downArrow.className = "fas fa-arrow-down nav-arrow";

        navWrapper.appendChild(upArrow);
        navWrapper.appendChild(downArrow);

        const cancelSearchBtn = document.createElement("i");
        cancelSearchBtn.className = "fas fa-times cancel-search";

        wrapper.appendChild(inputWrapper);
        wrapper.appendChild(navWrapper);
        wrapper.appendChild(cancelSearchBtn);

        titleEl.innerHTML = "";
        titleEl.appendChild(wrapper);
        input.focus();

        let currentIndex = -1;
        let matches = [];

        function doSearch(keyword) {
            matches = [];
            currentIndex = -1;
            const bubbles = chatContainer.querySelectorAll(".chat-bubble");

            bubbles.forEach(bubble => {
                bubble.innerHTML = bubble.textContent;
                if (keyword) {
                    const regex = new RegExp(`(${keyword})`, "gi");
                    if (regex.test(bubble.textContent)) {
                        matches.push(bubble);
                        bubble.innerHTML = bubble.textContent.replace(regex, `<span class="highlight">$1</span>`);
                    }
                }
            });

            if (matches.length > 0) {
                currentIndex = 0;
                scrollToMatch();
                noResult.style.display = "none";
                inputWrapper.classList.remove("no-match");
                matchCounter.style.display = "inline";
            } else {
                if (keyword) {
                    noResult.style.display = "inline-block";
                    inputWrapper.classList.add("no-match");
                } else {
                    noResult.style.display = "none";
                    inputWrapper.classList.remove("no-match");
                }
                matchCounter.style.display = "none";
                matchCounter.textContent = "";
            }
        }

        function scrollToMatch() {
            matches.forEach(b => b.classList.remove("current-highlight"));
            if (matches[currentIndex]) {
                matches[currentIndex].classList.add("current-highlight");
                matches[currentIndex].scrollIntoView({ behavior: "smooth", block: "center" });
                matchCounter.textContent = `${currentIndex + 1} of ${matches.length}`;
            }
        }

        input.addEventListener("input", () => {
            doSearch(input.value.trim());
        });

        input.addEventListener("keydown", (e) => {
            if (matches.length === 0) return;
            if (e.key === "ArrowDown" || e.key === "Enter") {
                currentIndex = (currentIndex + 1) % matches.length;
                scrollToMatch();
                e.preventDefault();
            }
            if (e.key === "ArrowUp") {
                currentIndex = (currentIndex - 1 + matches.length) % matches.length;
                scrollToMatch();
                e.preventDefault();
            }
        });

        upArrow.addEventListener("click", () => {
            if (matches.length > 0) { currentIndex = (currentIndex - 1 + matches.length) % matches.length; scrollToMatch(); }
        });
        downArrow.addEventListener("click", () => {
            if (matches.length > 0) { currentIndex = (currentIndex + 1) % matches.length; scrollToMatch(); }
        });

        clearIcon.addEventListener("click", () => {
            input.value = "";
            doSearch("");
            input.focus();
            inputWrapper.classList.remove("no-match");
            noResult.style.display = "none";
            matchCounter.style.display = "none";
        });

        cancelSearchBtn.addEventListener("click", () => {
            titleEl.innerHTML = originalHTML;
            if (backArrow) backArrow.style.display = "";
            if (dropdown) dropdown.style.display = "";

            const bubbles = chatContainer.querySelectorAll(".chat-bubble");
            bubbles.forEach(b => b.innerHTML = b.textContent);

            if (inputArea) inputArea.style.display = "";
        });
    }
});