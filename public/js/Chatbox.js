class Chatbox {
    static #booted = false;
    events = {};
    chatElement;
    chatIconElement;
    mainDiv;
    sendBtn;
    closeBtn;
    inputField;
    config = {};
    localStorageName = 'chatbox-config';
    init() {
        try {
            this.config = JSON.parse(localStorage.getItem(this.localStorageName));
            if (this.config === null) {
                throw 'empty config';
            }
        } catch (e) {
            this.config = {
                uuid: Chatbox.generateUUID(),
                boxClosed: true
            };
            this.saveConfig();
        }

        if (!Chatbox.#booted) {
            document.addEventListener("DOMContentLoaded", () => {
                // chatbox
                this.chatElement = document.createElement("div");
                this.chatElement.id = "chatbox";
                this.chatElement.className = this.config.boxClosed ? "d-none" : "";
                this.chatElement.innerHTML = `<div id="chatbox-close"></div>
                <div id="chatbox-list" class="custom-scroll"></div>
                <div id="chatbox-message">
                    <input id="chatbox-input" type="text" placeholder="Mesajul tau..." autocomplete="off"/>
                    <span id="chatbox-send">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 8c137 0 248 111 248 248S393 504 256 504 8 393 8 256 119 8 256 8zm-28.9 143.6l75.5 72.4H120c-13.3 0-24 10.7-24 24v16c0 13.3 10.7 24 24 24h182.6l-75.5 72.4c-9.7 9.3-9.9 24.8-.4 34.3l11 10.9c9.4 9.4 24.6 9.4 33.9 0L404.3 273c9.4-9.4 9.4-24.6 0-33.9L271.6 106.3c-9.4-9.4-24.6-9.4-33.9 0l-11 10.9c-9.5 9.6-9.3 25.1.4 34.4z"/></svg>
                    </span>
                </div>`;
                document.body.append(this.chatElement);
                this.mainDiv = document.getElementById("chatbox-list");
                this.inputField = document.getElementById("chatbox-input");
                this.sendBtn = document.getElementById("chatbox-send");
                this.closeBtn = document.getElementById("chatbox-close");
                this.inputField.addEventListener("keydown", (e) => {
                    if (e.code === "Enter") {
                        this.send();
                    }
                });
                this.sendBtn.addEventListener("click", () => this.send());
                this.closeBtn.addEventListener("click", () => this.toggleBox(false));

                // chaticon
                this.chatIconElement = document.createElement("div");
                this.chatIconElement.id = "chatbox-icon";
                this.chatIconElement.className = this.config.boxClosed ? "" : "d-none";
                this.chatIconElement.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 32C114.6 32 0 125.1 0 240c0 49.6 21.4 95 57 130.7C44.5 421.1 2.7 466 2.2 466.5c-2.2 2.3-2.8 5.7-1.5 8.7S4.8 480 8 480c66.3 0 116-31.8 140.6-51.4 32.7 12.3 69 19.4 107.4 19.4 141.4 0 256-93.1 256-208S397.4 32 256 32zM128 272c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zm128 0c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zm128 0c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32z"/></svg>';
                this.chatIconElement.addEventListener("click", () => this.toggleBox(true));
                document.body.append(this.chatIconElement);
            });
            Chatbox.#booted = true;
        }

        this.event('init');
    }
    setDriver(driver) {
        this.events = {};
        driver.init(this);
        this.init();
    }
    event(name, ...params) {
        if (name in this.events) {
            for (let i in this.events[name]) {
                if (typeof this.events[name][i] === 'function') {
                    if (this.events[name][i].call(this, ...params) === false) { // stop propagation
                        break;
                    }
                }
            }
        }
    }
    on(name, callback) {
        if (!(name in this.events)) {
            this.events[name] = [];
        }
        this.events[name].push(callback);
    }
    send() {
        let input = this.inputField.value;
        if (input === '') {
            return;
        }
        this.inputField.value = "";
        this.inputField.focus();
        this.addChat(input)
        this.event('send', input);
    }
    addChat(input, date) {
        let userDiv = document.createElement("div");
        date = date || (new Date).toLocaleTimeString();
        userDiv.className = "chatbox-user";
        userDiv.innerHTML = `<span class="chatbox-response">${Chatbox.htmlEncode(input)}</span><small>${date}</small>`;
        this.mainDiv.appendChild(userDiv);
        this.mainDiv.scrollTop = this.mainDiv.scrollHeight;
    }
    addReply(reply) {
        let replyDiv = document.createElement("div");
        this.stopTyping();
        reply.date = reply.date || (new Date).toLocaleTimeString();
        replyDiv.className = "chatbox-reply";
        replyDiv.innerHTML = `<span class="chatbox-response">${reply.text}</span><small>${reply.date}</small>`;
        this.mainDiv.appendChild(replyDiv);
        if (reply.options) {
            this.addOptions(reply.options);
        }
        this.mainDiv.scrollTop = this.mainDiv.scrollHeight;
        this.event('reply', reply);
    }
    startTyping() {
        let typingElement = document.getElementById("chatbox-typing");
        if (typingElement) {
            return;
        }
        let divTyping = document.createElement("div");
        divTyping.id = "chatbox-typing";
        divTyping.className = "chatbox-reply";
        divTyping.innerHTML = `<span class="chatbox-response">...</span>`;
        this.mainDiv.appendChild(divTyping);
        this.mainDiv.scrollTop = this.mainDiv.scrollHeight;
        this.event('start-typing');
    }
    stopTyping() {
        let typingElement = document.getElementById("chatbox-typing");
        if (typingElement) {
            typingElement.remove();
            this.event('stop-typing');
        }
    }
    addOptions(options) {
        let optionsDiv = document.createElement("div");
        optionsDiv.className = "chatbox-options";
        for (let i in options) {
            let option = options[i],
                button = document.createElement("button");
            button.className = "chat-button";
            button.onclick = () => {
                this.inputField.value = option.value ? option.value : option.name;
                this.send();
            };
            button.innerHTML = option.name;
            optionsDiv.appendChild(button);
        }
        this.mainDiv.appendChild(optionsDiv);
    }
    getUuid() {
        return this.config.uuid;
    }
    toggleBox(opened = true) {
        this.chatElement.classList.toggle('d-none', !opened);
        this.chatIconElement.classList.toggle('d-none', opened);
        this.config.boxClosed = !opened;
        this.saveConfig();
    }
    saveConfig() {
        localStorage.setItem(this.localStorageName, JSON.stringify(this.config));
    }
    static generateUUID() {
        var d = new Date().getTime();
        var d2 = ((typeof performance !== 'undefined') && performance.now && (performance.now()*1000)) || 0;
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16;
            if (d > 0){
                r = (d + r)%16 | 0;
                d = Math.floor(d/16);
            } else {
                r = (d2 + r)%16 | 0;
                d2 = Math.floor(d2/16);
            }
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }
    static htmlEncode(str) {
        return String(str).replace(/[^\w. ]/gi, function(c) {
            return '&#' + c.charCodeAt(0) + ';';
        });
    }
}
