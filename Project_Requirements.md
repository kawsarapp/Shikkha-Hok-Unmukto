

**Project Overview:**
Build a highly optimized, enterprise-grade, video-less EdTech platform. The system is heavily reliant on AI-generated text content, automated Q&A, strict spaced-repetition learning, and a highly concurrent live exam engine.

**Core Tech Stack & Architecture:**

* **Backend Framework:** Laravel 11+ (RESTful API approach mixed with Inertia).
* **Frontend:** Vue 3 (Composition API) + Inertia.js + Tailwind CSS + Pinia (for state management).
* **Database:** MySQL 8.0+ (Strict mode enabled).
* **Caching & Queues:** Redis (Crucial for Live Leaderboard, Session management, and Laravel Horizon for Queue monitoring).
* **AI Integration:** Google Gemini 1.5 API (for Text/JSON generation) & Web Speech API / ElevenLabs (for TTS).

---

### Module-by-Module Technical Specifications:

#### 1. Advanced Security & Anti-Cheat (Frontend & Backend)

* **Session Management:** Implement strict One-Device Login. Use Laravel's `database` or `redis` session driver. On new login, invalidate previous session ID.
* **Anti-Piracy (Content Protection):**
* CSS: `user-select: none;`
* JS: Prevent context menu (`contextmenu` event), block common keyboard shortcuts (Ctrl+C, Ctrl+P, F12) using event listeners.
* **Dynamic Canvas Watermarking:** Render a transparent HTML5 `<canvas>` overlay on top of the text. Draw the logged-in user's Name + Phone Number + IP Address dynamically. Animate it slowly across the screen to prevent cropping.


* **Exam Anti-Cheat (Page Visibility API):** If a student switches tabs or minimizes the browser during a live exam, trigger a warning. On 3 warnings, auto-submit the exam.

#### 2. AI Automation Engine & Background Processing (The Brain)

* **Queue Architecture:** All file processing MUST be offloaded to Laravel Queues (Redis driver). Implement Failed Job handling (retry 3 times with backoff).
* **Document Parsing:** Use spatie/pdf-to-text or similar PHP packages to extract raw text from uploaded files before sending to Gemini API.
* **AI Prompt Engineering & JSON Formatting:**
* When querying Gemini for Q&A, strictly pass `response_mime_type: "application/json"` to enforce valid JSON output.
* The prompt must instruct the AI to generate: `question`, `options` (array of 4), `correct_option_index`, and `detailed_explanation`.


* **AI Chatbot (RAG Architecture simplified):** When a student asks a question on a study page, DO NOT send the entire document to Gemini every time (wastes tokens). Send only the specific chapter's text content + the user's question as the context prompt.

#### 3. Strict Progression & Gamified Learning Engine

* **Mastery Lock (Middleware/Policies):** Create a Laravel Route Middleware (`CheckChapterUnlock`). It queries the `course_progress` table. If the prerequisite chapter's score is < 70%, return a 403 Forbidden with a JSON error.
* **Time-Locked Progression:**
* Frontend: Use `IntersectionObserver` to track actual reading time (not just tab open time). Send periodic ping (every 60s) to backend to update `time_spent_reading_seconds`.
* Only enable the "Take Exam" button when time > 300 seconds.


* **Spaced Repetition System (SRS):** Run a Laravel Daily Scheduled Task (`Cron`). Find all incorrectly answered questions from the last 7 days for each user. Generate a "Daily Weakness Quiz". Lock frontend routes until this specific exam_id is completed.

#### 4. High-Concurrency Live Exam System

* **Timer Sync:** Do not rely solely on JS `setInterval`. Fetch the server's current timestamp (NTP synced) on load. Calculate `endTime - serverTime`.
* **Auto-Save mechanism:** Send an async Axios POST request every 30 seconds to `/api/exam/autosave` containing the current JSON payload of selected answers. Store this in Redis for blazing-fast writes, persist to MySQL only on final submission.
* **Instant Leaderboard:** Do not query MySQL with heavy `GROUP BY` for the live leaderboard. Use Redis Sorted Sets (`ZADD leaderboard:exam_id score user_id`). This allows fetching top 100 ranks in O(log(N)) time.

#### 5. AI Voice Engine & Audio Player (Vue Component)

* **Floating Player UI:** Build a globally mounted Vue component (`<AudioPlayer/>`) outside the Inertia `<slot>`, so navigating between pages does not interrupt playback.
* **TTS Integration:** Use standard `window.speechSynthesis`. Chunk the text into sentences (split by `. `) to prevent the API from dropping long paragraphs.
* **MediaSession API:** Register navigator.mediaSession handlers (play, pause, seekbackward) so students can control audio from their phone's lock screen.

#### 6. Enterprise-Grade Database Schema (MySQL)

Ensure all foreign keys have proper `ON DELETE CASCADE` or `RESTRICT` rules. Use standard Laravel timestamps and soft deletes.

* `users`: id, name, phone, password, role(enum), device_token, study_streak(int), locked_cooldown_until(timestamp), created_at.
* `courses`: id, title, slug, is_published(boolean).
* `chapters`: id, course_id(FK), order_index(int), title, min_reading_time_seconds(int), passing_score_percentage(int).
* `study_materials`: id, chapter_id(FK), content(longtext), ai_summary(text), audio_flag(boolean).
* `questions`: id, chapter_id(FK), type(enum: normal, srs), question_text, options(json), correct_option(string), explanation(text).
* `exams`: id, chapter_id(FK nullable), title, is_live(boolean), start_time(datetime), duration_minutes(int), negative_mark_value(decimal).
* `exam_attempts`: id, user_id(FK), exam_id(FK), score(decimal), correct_count(int), wrong_count(int), is_completed(boolean), started_at, submitted_at.
* `user_answers`: id, attempt_id(FK), question_id(FK), selected_option, is_correct(boolean).
* `course_progress`: id, user_id(FK), chapter_id(FK), is_unlocked(boolean), highest_score(decimal), time_spent_reading_seconds(int).

**Instruction for AI:** Start by initializing the Laravel 11 project, installing Vue/Inertia, and generating the exact database migrations based on the schema above.

7. Strict Progression & "Force Learning" Module (Gamified Discipline)

Prerequisite Unlocking (Mastery System): The courses must be strictly linear. Chapter 2 remains completely locked until the student scores a minimum threshold (e.g., 60% or 70%) in the Chapter 1 Assessment.

Minimum Reading Time Lock: The "Start Exam" button on the study material page must remain disabled (grayed out) until the student spends a minimum required time (e.g., 5 minutes) actively scrolling/reading the page. (Use JavaScript Intersection Observer and timers).

Spaced Repetition System (SRS): AI tracks which specific topics/questions a student frequently gets wrong. The system will forcefully generate a "Daily Weakness Quiz". The student cannot start new chapters until they complete this daily review.

Failure Cooldown Penalty: If a student fails the same exam 3 times consecutively, a "Cooldown Timer" (e.g., 2 hours) is activated. During this time, they are forced to go back and re-read the AI-generated study material.

Sudden Death Exam Mode (Hardcore): An optional exam mode where the exam instantly terminates if the student gives 3 wrong answers in a row, forcing them to be extra careful.

8. AI Voice Engine (Audio Learning & TTS Module)

Advanced Text-to-Speech (TTS) Integration: Integrate Web Speech API (Free) or external APIs (Google Cloud TTS / ElevenLabs) to convert the AI-generated study materials and summaries into high-quality human-like audio.

Podcast-Style Sticky Audio Player:

Implement a persistent, floating audio player (similar to Spotify) at the bottom of the UI.

Students can hit 'Play' to listen to the entire study material while walking, commuting, or resting their eyes.

Controls must include: Play/Pause, 10-second rewind/forward, and Playback Speed (1x, 1.25x, 1.5x, 2x).

Background Playback Support: Utilize the MediaSession API so that the audio continues playing in the background even if the student locks their phone screen or switches tabs.

Audio Explanations for Exams: On the exam result page, add a "Listen to Explanation" button next to wrong answers. The system will read out the AI-generated reason for why the answer was wrong and what the correct logic is.

9. Updated Database Schema Additions (MySQL)

Users Table: Add columns -> daily_audio_minutes_listened, locked_cooldown_until (Timestamp).

Course_Progress Table: user_id, chapter_id, is_unlocked (Boolean), time_spent_reading_seconds.

Study_Materials Table: Add column -> audio_url (To store pre-generated TTS audio files to save API costs, or flag for dynamic browser synthesis).
---


10. UI/UX Design System & Tailwind Configuration

Design Philosophy: The UI must be ultra-minimal, distraction-free, and highly focused on readability. Think of a blend between Medium.com (for reading) and Duolingo (for gamification).

Color Palette (Tailwind Config):

Primary Accent: Indigo or Emerald Green (Used for primary buttons, active states, and success indicators).

Background (Light Mode): Soft off-white (#F9FAFB - Tailwind gray-50) to reduce eye strain. Text should be gray-900.

Background (Dark Mode): Deep Slate/Navy (#0F172A - Tailwind slate-900). Text should be slate-200.

Danger/Warning: Rose or Red (Used for negative marking, wrong answers, and time-running-out alerts).

Default Theme Strategy:

Implement a system-wide Dark/Light mode toggle using Tailwind's darkMode: 'class' strategy.

Crucial: The "Smart Text Reader" page MUST have its own independent theme toggle, allowing a student to read in dark mode even if the rest of the site is in light mode.

Typography:

English Font: Use 'Inter' or 'Plus Jakarta Sans' for a modern, crisp look.

Bengali Font Support (Important): Add 'Noto Sans Bengali' or 'Hind Siliguri' as the primary font family in CSS for all Bengali text rendering to ensure correct ligature display.

Line Height & Spacing: Use loose line heights (leading-relaxed or leading-loose) on the reading pages to make long paragraphs easy to digest.

Component Styling:

Use soft rounded corners (rounded-xl or rounded-2xl) for cards and modals.

Use subtle shadows (shadow-sm or shadow-md) in light mode; use subtle borders (border-slate-800) instead of shadows in dark mode.

Exam Interface: Must be strictly full-screen on mobile, hiding the navbar and footer to prevent accidental clicks. The timer must be sticky at the top center of the screen (sticky top-0 z-50 backdrop-blur-md).

Animations & Transitions:

Keep it professional. Use Vue/Alpine <Transition> for smooth fade-ins on page loads and modal openings (duration-200 ease-in-out).

Add a satisfying subtle "pop" animation when a user selects a correct answer in the SRS quiz.

Final Instruction for the AI Agent: You now have the complete PRD. Please acknowledge these instructions. When you are ready to begin, start by setting up the Laravel backend, installing Inertia + Vue, and generating the database migrations strictly following the provided schema. Do not generate fake data yet; just set up the architecture and the UI skeleton.


11. 100% Dynamic Architecture & "Zero Hardcoding" Policy

Strict Rule: Absolutely NO hardcoded values for system rules, API configurations, pricing, thresholds, or AI prompts in the controllers or frontend components. Everything must be manageable via the Admin Dashboard.

Global Settings Key-Value Store: Implement a dynamic settings table in MySQL (id, key_name, value, type). Create a Singleton or Laravel Cache helper to load these settings efficiently on every request.

Dynamic AI Configuration:

Customizable Prompts: The system prompts sent to Gemini API (e.g., "Act as a teacher and summarize this...") MUST be editable from the Admin Panel.

Model Selection: Admin can switch between gemini-1.5-flash and gemini-1.5-pro dynamically based on cost/performance needs.

Dynamic Exam & Progression Rules:

Admin can dynamically set the minimum_reading_time_seconds (e.g., change it from 300s to 120s without touching the code).

Admin can adjust the passing_score_percentage (e.g., 60% or 80%) globally or per chapter.

Admin can configure the failure_cooldown_minutes (e.g., how long a student is locked out after failing 3 times).

The negative_mark_value must be adjustable per exam.

Dynamic Gamification & Pricing:

Coin reward amounts, daily streak milestone bonuses, and badge criteria must be editable.

Freemium subscription prices (e.g., 20 BDT or 50 BDT) and BKash/Nagad API credentials must be securely editable via the admin UI (stored securely, potentially in .env managed via a UI package, or encrypted in the DB).

Dynamic Ad Management (AdSense/Sponsors):

Admin must have a dedicated "Ad Management" interface to paste Google AdSense script tags or custom HTML banner codes.

Admin can toggle visibility (Show/Hide) for specific ad slots (e.g., sidebar_ad_active, in_content_ad_active) instantly.

Dynamic UI & Localization:

Important frontend texts (Hero section title, marketing taglines, footer links) must be editable from the backend.

Color Theming (Optional but recommended): Store primary branding color hex codes in the settings table and inject them into Tailwind via CSS variables on load, so the admin can change the brand color without rebuilding assets.