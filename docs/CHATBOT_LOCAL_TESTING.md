# Local Chatbot End-to-End Testing Guide

This guide explains how to simulate a real client website on your local machine and verify the full production workflow:

Create chatbot → Train → Customize → Embed → Chat → Conversation/History → AI Bot Messages → AI Bot Enquiries

No chatbot or enquiry business logic is changed by this test page.

---

## 1. Where to paste the generated embed script

Edit:

`resources/views/chatbot-test.blade.php`

Paste the script under the HTML comment in `<head>` that says:

`PASTE GENERATED EMBED SCRIPT BELOW THIS COMMENT`

Example shape (values come from your dashboard Embed step):

```html
<script
    defer
    src="http://127.0.0.1:8000/vendor/chatbot/js/external-chatbot.js"
    data-chatbot-uuid="YOUR-CHATBOT-UUID"
    data-iframe-width="420"
    data-iframe-height="745"
    data-language="en"
></script>
```

Important:

- The script `src` origin becomes the chatbot host for iframe + API calls.
- Use your **local** Laravel origin (for example `http://127.0.0.1:8000`), not production.
- Copy the UUID from Dashboard → AI Bots → Embed step.

---

## 2. Laravel URL to open

After `php artisan serve`:

[http://127.0.0.1:8000/chatbot-test](http://127.0.0.1:8000/chatbot-test)

Named route: `chatbot.test`

---

## 3. How to run Laravel locally

```bash
cd /Users/rommankhan/developement-nexbuddy
php artisan config:clear
php artisan serve
```

Use the same PHP binary you normally use for this project (Homebrew PHP 8.5 or XAMPP PHP).

Also open the admin panel (login required), typically:

[http://127.0.0.1:8000/dashboard/chatbot](http://127.0.0.1:8000/dashboard/chatbot)

---

## 4. How to test the chatbot

1. In the admin panel, create a chatbot (or use an existing one).
2. Train it if needed (website / text / Q&A / file).
3. Customize appearance if needed.
4. Open the **Embed** step and copy the generated script.
5. Paste it into `resources/views/chatbot-test.blade.php` (local `src` origin).
6. Open `/chatbot-test` and refresh.
7. Click the chatbot trigger and send messages.

### Example test conversations

**Basic chat**

- Visitor: `Hi, I need help with your product.`

**Enquiry-qualifying chat** (should trigger existing EnquiryDetectorService)

- Visitor: `My name is Priya Sharma. Email is priya.sharma@example.com. Phone +91 98765 43210. I work at Nexgeno Labs. Can I book a demo and get pricing?`

**Callback request**

- Visitor: `Please call me back tomorrow about enterprise pricing. Company: Acme Retail. Email: ops@acmeretail.test`

---

## 5. How to verify conversations and history

### In the UI

1. Open Dashboard → AI Bots.
2. Open **AI Bot Messages**.
3. Confirm the new conversation appears in the left list.
4. Click it and confirm message history is visible.

### In the database

Expect new rows in:

| Table | What to check |
|-------|----------------|
| `ext_chatbots` | Existing chatbot (already created) |
| `ext_chatbot_conversations` | New conversation (`chatbot_id`, `session_id`, `is_showed_on_history`) |
| `ext_chatbot_histories` | User/assistant messages for that `conversation_id` |
| `ext_chatbot_customers` | Optional customer/contact record when collected |

Quick SQL checks:

```sql
SELECT id, chatbot_id, conversation_name, is_showed_on_history, created_at
FROM ext_chatbot_conversations
ORDER BY id DESC
LIMIT 5;

SELECT id, conversation_id, role, LEFT(message, 120) AS message, created_at
FROM ext_chatbot_histories
ORDER BY id DESC
LIMIT 20;
```

---

## 6. How to verify AI Bot Enquiries

After a qualifying conversation (email/phone/company + interest keywords):

### In the UI

1. Dashboard → **AI Bot Enquiries**
2. Confirm a new enquiry row appears (visitor, email/phone/company, interest, lead score, status).
3. Optionally open the related conversation from the enquiry actions.

### In the database

| Table | What to check |
|-------|----------------|
| `ext_chatbot_enquiries` | New/updated enquiry for the conversation |

```sql
SELECT id, conversation_id, email, phone, company, interest, lead_score, status, created_at
FROM ext_chatbot_enquiries
ORDER BY id DESC
LIMIT 5;
```

Enquiry detection uses the existing `EnquiryDetectorService` hooked from `ChatbotHistory` creation. Do not modify it for this test.

---

## 7. Asset checklist

On `/chatbot-test`, browser Network tab should successfully load:

- `/vendor/chatbot/js/external-chatbot.js`
- Chatbot frame: `/chatbot/{uuid}/frame`
- Chatbot API: `/api/v2/chatbot/{uuid}`

If the widget does not appear:

- Confirm the script was pasted and the page was refreshed.
- Confirm `src` points to the local origin.
- Confirm the chatbot UUID exists and the bot is active.
- Confirm trusted domains allow local host (or leave trusted domains empty).

---

## 8. Regression checklist (unchanged production behavior)

Confirm these still work from the admin panel exactly as before:

- [ ] AI Bot Messages
- [ ] AI Bot Contacts
- [ ] AI Bot Analytics
- [ ] AI Bot Knowledge Base
- [ ] AI Bot Canned Responses
- [ ] Training
- [ ] Embed script generation
- [ ] Widget on `/chatbot-test`
- [ ] Conversation history
- [ ] Existing chatbot APIs (`/api/v2/chatbot/...`)
- [ ] AI Bot Enquiries listing / status updates

This testing page only hosts the embed script. It does not change controllers, models, services, migrations, enquiry logic, or production routes.
