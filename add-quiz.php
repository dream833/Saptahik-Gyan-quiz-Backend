<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'quizauth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Create Quiz</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #eef1f6;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 900px;
        background: #fff;
        margin: auto;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    }
    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }
    label {
        font-weight: 600;
        display: block;
        margin-top: 15px;
        color: #555;
    }
    input, textarea, select {
        width: 100%;
        padding: 10px;
        margin-top: 6px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    input:focus, textarea:focus, select:focus {
        border-color: #4a90e2;
        outline: none;
        box-shadow: 0 0 6px rgba(74,144,226,0.3);
    }
    textarea { resize: vertical; }
    .btn {
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 15px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 15px;
    }
    .btn-next { background: #4a90e2; color: white; }
    .btn-next:hover { background: #357abd; }
    .btn-back { background: #999; color: white; }
    .btn-back:hover { background: #777; }
    .btn-save { background: #28a745; color: white; float: right; }
    .btn-save:hover { background: #218838; }
    .btn-remove {
        background: #dc3545; color: white;
        padding: 5px 10px; border-radius: 6px; font-size: 13px;
        position: absolute; right: 10px; top: 10px;
    }
    .btn-remove:hover { background: #b52a37; }
    .question-block {
        background: #f8f9fc;
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #e3e6f0;
        margin-top: 15px;
        position: relative;
    }
    .hidden { display: none; }
    .muted { color: #666; font-size: 13px; }
    @media(max-width: 768px) {
        .container { padding: 15px; }
        .btn-save { width: 100%; float: none; }
    }
</style>
</head>
<body>

<div class="container">
    <h1>📝 Create New Quiz</h1>

    <!-- Step 1 -->
    <div id="step1">
        <label>📌 Quiz Title</label>
        <input type="text" id="quiz_title" placeholder="Enter quiz title" autocomplete="off">

        <label>📝 Description</label>
        <textarea id="quiz_desc" placeholder="Enter quiz description"></textarea>

        <label>⏳ Timer (in seconds)</label>
        <input type="number" id="quiz_timer" placeholder="e.g. 300" min="1" inputmode="numeric">
        <!-- <span class="muted">* Date auto-set hobe (today)</span> -->

        <button class="btn btn-next" onclick="goToStep2()">Next ➡️</button>
    </div>

    <!-- Step 2 -->
    <div id="step2" class="hidden">
        <div id="questions_area">
            <div class="question-block">
                <button type="button" class="btn-remove" onclick="removeQuestion(this)">❌ Remove</button>
                <label>❓ Question</label>
                <textarea placeholder="Enter question"></textarea>

                <label>Option A</label>
                <input type="text" placeholder="Option A">

                <label>Option B</label>
                <input type="text" placeholder="Option B">

                <label>Option C</label>
                <input type="text" placeholder="Option C">

                <label>Option D</label>
                <input type="text" placeholder="Option D">

                <label>✅ Correct Answer</label>
                <select>
                    <option value="">Select Correct Option</option>
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>
            </div>
        </div>

        <button class="btn btn-back" onclick="goToStep1()">⬅️ Back</button>
        <button class="btn btn-next" type="button" onclick="addQuestion()">+ Add Question</button>
        <button id="saveBtn" class="btn btn-save" onclick="saveQuiz()">💾 Save Quiz</button>
    </div>
</div>

<script>
    function goToStep2() {
        const title = document.getElementById("quiz_title").value.trim();
        const desc = document.getElementById("quiz_desc").value.trim();
        const timer = document.getElementById("quiz_timer").value.trim();

        if (!title || !desc || !timer) {
            alert("⚠️ Please fill all details before proceeding.");
            return;
        }
        if (isNaN(timer) || Number(timer) <= 0) {
            alert("⚠️ Timer must be a valid number in seconds.");
            return;
        }

        document.getElementById("step1").classList.add("hidden");
        document.getElementById("step2").classList.remove("hidden");
    }

    function goToStep1() {
        document.getElementById("step2").classList.add("hidden");
        document.getElementById("step1").classList.remove("hidden");
    }

    function addQuestion() {
        const qBlock = document.createElement('div');
        qBlock.classList.add('question-block');
        qBlock.innerHTML = `
            <button type="button" class="btn-remove" onclick="removeQuestion(this)">❌ Remove</button>
            <label>❓ Question</label>
            <textarea placeholder="Enter question"></textarea>
            <label>Option A</label>
            <input type="text" placeholder="Option A">
            <label>Option B</label>
            <input type="text" placeholder="Option B">
            <label>Option C</label>
            <input type="text" placeholder="Option C">
            <label>Option D</label>
            <input type="text" placeholder="Option D">
            <label>✅ Correct Answer</label>
            <select>
                <option value="">Select Correct Option</option>
                <option value="A">Option A</option>
                <option value="B">Option B</option>
                <option value="C">Option C</option>
                <option value="D">Option D</option>
            </select>
        `;
        document.getElementById('questions_area').appendChild(qBlock);
    }

    function removeQuestion(btn) {
        const container = document.getElementById('questions_area');
        if (container.children.length === 1) {
            alert("⚠️ At least one question is required.");
            return;
        }
        btn.parentElement.remove();
    }

   async function saveQuiz() {
    const title = document.getElementById("quiz_title").value.trim();
    const desc = document.getElementById("quiz_desc").value.trim();
    const timerVal = document.getElementById("quiz_timer").value.trim();
    const timer = Number(timerVal);

    if (!title || !desc || !timerVal || isNaN(timer) || timer <= 0) {
        alert("⚠️ Please fill quiz title, description and a valid timer.");
        return;
    }

    const blocks = document.querySelectorAll("#questions_area .question-block");
    if (blocks.length === 0) {
        alert("⚠️ Please add at least one question.");
        return;
    }

    const questions = [];
    for (const block of blocks) {
        const q = block.querySelector("textarea").value.trim();
        const inputs = block.querySelectorAll("input");
        const a = inputs[0].value.trim();
        const b = inputs[1].value.trim();
        const c = inputs[2].value.trim();
        const d = inputs[3].value.trim();
        const correct = block.querySelector("select").value;

        if (!q || !a || !b || !c || !d || !correct) {
            alert("⚠️ Please fill all question details (question, 4 options, correct).");
            return;
        }

        // ✅ এখানে ছিল ভুল; এখন ঠিক করা হলো
        questions.push({
            question: q,
            option_a: a,
            option_b: b,
            option_c: c,
            option_d: d,
            correct_option: correct
        });
    } // <-- for loop properly বন্ধ

    const payload = {
        title: title,
        description: desc,
        timer: timer,
        questions: questions
        // quiz_date পাঠানো হবে না; backend CURDATE() নেবে
    };

    const saveBtn = document.getElementById("saveBtn");
    saveBtn.disabled = true;
    saveBtn.textContent = "Saving...";

    try {
        const res = await fetch("api/create-quiz.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.status === "success") {
            alert("✅ " + data.message + (data.quiz_date ? " (Date: " + data.quiz_date + ")" : ""));
            // Reset form
            document.getElementById("quiz_title").value = "";
            document.getElementById("quiz_desc").value = "";
            document.getElementById("quiz_timer").value = "";
            document.getElementById("questions_area").innerHTML = `
                <div class="question-block">
                    <button type="button" class="btn-remove" onclick="removeQuestion(this)">❌ Remove</button>
                    <label>❓ Question</label>
                    <textarea placeholder="Enter question"></textarea>
                    <label>Option A</label>
                    <input type="text" placeholder="Option A">
                    <label>Option B</label>
                    <input type="text" placeholder="Option B">
                    <label>Option C</label>
                    <input type="text" placeholder="Option C">
                    <label>Option D</label>
                    <input type="text" placeholder="Option D">
                    <label>✅ Correct Answer</label>
                    <select>
                        <option value="">Select Correct Option</option>
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>
            `;
            goToStep1();
        } else {
            alert("❌ " + (data.message || "Failed to create quiz"));
        }
    } catch (err) {
        alert("❌ API Error: " + err.message);
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = "💾 Save Quiz";
    }
}
</script>

</body>
</html>