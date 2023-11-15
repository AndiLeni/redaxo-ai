import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
// import { TranslateClient, TranslateTextCommand } from "@aws-sdk/client-translate";

Alpine.plugin(persist);

// custom alpinejs
// Assign a custom prefix:
Alpine.prefix("ai-");
// Don't assign Alpine to the window (keep it private):
window.Alpine = Alpine;

// global state
Alpine.store("ai", {
    modalOpen: false,

    init() {
        this.modal = document.getElementById("aiModal");
        this.aiMenuBtn = document.getElementById("aiMenuBtn");
        this.aiMenuBtn.addEventListener("click", function () {
            Alpine.store("ai").modalToggle();
        });
    },

    modalToggle() {
        this.modalOpen = !this.modalOpen;
    },
});

Alpine.data("aiModal", () => ({
    tab: Alpine.$persist("chatgpt").as("ai_tab"),

    translateSourceLang: "de",
    translateTargetLang: "en",
    translateIn: "",
    translateOut: "",

    chatIn: "",
    messages: Alpine.$persist([]).as("ai_messages"),
    chatLoading: false,

    async init() {},

    clearChat() {
        if (confirm("Chat leeren? (kann nicht rückgängig gemacht werden)")) {
            this.messages = [];
        }
    },

    async translate() {
        let response = await fetch(`https://translation.googleapis.com/language/translate/v2`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-goog-api-key": rex.ai_gcpKey,
            },
            mode: "cors",
            body: JSON.stringify({
                q: this.translateIn,
                source: this.translateSourceLang,
                target: this.translateTargetLang,
            }),
        });

        let data = await response.json();
        // console.log(data);

        this.translateOut = response.data.translations[0].translatedText;
    },

    sendChatMsg(event = undefined) {
        if (event !== undefined && event.shiftKey) {
            return;
        }

        var msg_input = this.chatIn;
        this.messages.push({
            role: "user",
            content: msg_input,
        });
        this.chatIn = "";

        this.chat(msg_input);
    },

    async chat() {
        this.chatLoading = true;

        let response = await fetch("https://api.openai.com/v1/chat/completions", {
            method: "POST",
            headers: {
                Authorization: `Bearer ${rex.ai_openaiKey}`,
                "Content-Type": "application/json",
            },
            mode: "cors",
            body: JSON.stringify({
                model: "gpt-3.5-turbo-16k",
                messages: this.messages,
                temperature: 0.1,
                n: 1,
                stream: false,
                stop: "stop",
            }),
        });

        this.chatLoading = false;

        let jsonData = await response.json();
        // console.log(jsonData);

        if ("error" in jsonData) {
            alert(jsonData.error.message);
        } else {
            // this.updateCostsAndTokens(jsonData.usage.total_tokens);

            let message_obj = jsonData.choices[0].message;

            // message_obj.content = this.md.render(message_obj.content);

            // console.log(message_obj);
            this.messages.push(message_obj);

            this.chatIn = "";
        }
    },
}));

Alpine.start();

$("#aiModalTabs a").click(function (e) {
    e.preventDefault();
    $(this).tab("show");
});
