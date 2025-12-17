@extends('layouts.app')
@section('content')

<form method="POST" class="report-schedule-form">
    @csrf

    {{-- Schedule Type --}}
    <div class="form-group">
        <label for="schedule_type">Schedule Type</label>
        <select name="schedule_type" id="schedule_type" required>
            <option value="weekly" selected>Weekly</option>
            <option value="monthly">Monthly</option>
        </select>
    </div>

    {{-- WEEKLY FORM --}}
    <div id="weekly-form">

        <div class="form-group">
            <label for="day">Day of Week</label>
            <select name="day" id="day">
                <option value="0">Sunday</option>
                <option value="1">Monday</option>
                <option value="2">Tuesday</option>
                <option value="3">Wednesday</option>
                <option value="4">Thursday</option>
                <option value="5">Friday</option>
                <option value="6">Saturday</option>
            </select>
        </div>

    </div>

    {{-- MONTHLY FORM --}}
    <div id="monthly-form" style="display:none;">

        <div class="form-group">
            <label for="month_day">Day of Month</label>
            <select name="month_day" id="month_day">
                @for ($i = 1; $i <= 31; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

    </div>

    {{-- TIME (SHARED) --}}
    <div class="form-row">
        <div class="form-group">
            <label for="hour">Hour</label>
            <select name="hour" id="hour" required>
                @for ($i = 0; $i < 24; $i++)
                    <option value="{{ $i }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                @endfor
            </select>
        </div>

        <div class="form-group">
            <label for="minute">Minute</label>
            <select name="minute" id="minute" required>
                @for ($i = 0; $i < 60; $i++)
                    <option value="{{ $i }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                @endfor
            </select>
        </div>
    </div>

    <button type="submit" class="btn-primary">Save Schedule</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const scheduleType = document.getElementById("schedule_type");
    const weeklyForm = document.getElementById("weekly-form");
    const monthlyForm = document.getElementById("monthly-form");

    function toggleForms() {
        if (scheduleType.value === "weekly") {
            weeklyForm.style.display = "block";
            monthlyForm.style.display = "none";
        } else {
            weeklyForm.style.display = "none";
            monthlyForm.style.display = "block";
        }
    }

    scheduleType.addEventListener("change", toggleForms);

    // initial load
    toggleForms();
});
</script>

<style>
.report-schedule-form {
    max-width: 600px;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-left: 155px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 12px;
}

label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

select {
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

select:focus {
    outline: none;
    border-color: #2563eb;
}

.btn-primary {
    margin-top: 10px;
    width: 100%;
    background: #2563eb;
    color: #fff;
    padding: 10px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover {
    background: #1e40af;
}


.report-schedule-form {
    max-width: 600px;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);

    /* CENTER FORM */
    margin: 40px auto;
}

/* FORM GROUPS */
.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

/* HOUR + MINUTE ROW */
.form-row {
    display: flex;
    gap: 12px;
}

/* LABEL */
label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

/* SELECT */
select {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    font-size: 14px;
}

select:focus {
    outline: none;
    border-color: #2563eb;
}

/* BUTTON */
.btn-primary {
    margin-top: 10px;
    width: 100%;
    background: #2563eb;
    color: #fff;
    padding: 12px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover {
    background: #1e40af;
}

/* ============================= */
/* MOBILE RESPONSIVE FIXES */
/* ============================= */
@media (max-width: 576px) {

    .report-schedule-form {
        margin: 20px 16px;
        padding: 16px;
    }

    .form-row {
        flex-direction: column;
    }

    label {
        font-size: 13px;
    }

    select {
        font-size: 13px;
    }

    .btn-primary {
        padding: 14px;
        font-size: 15px;
    }
}

    </style>

@endsection