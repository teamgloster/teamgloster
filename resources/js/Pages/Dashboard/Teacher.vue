<template>
    <Head title="Teacher Dashboard - TNHS" />
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar" :class="{ 'mobile-open': isMobileMenuOpen }">
            <div class="sidebar-header">
                <img :src="logo" alt="TNHS Logo" class="logo" />
                <div class="school-info">
                    <h2>TNHS</h2>
                    <p>Teacher Portal</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-group">
                    <span class="nav-group-label">Main</span>
                    <a
                        href="#"
                        class="nav-item"
                        :class="{ active: activeNav === 'dashboard' }"
                        @click.prevent="handleNavClick('dashboard')"
                    >
                        <LayoutDashboard class="nav-icon" :size="20" />
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Classes</span>
                    <a
                        href="#"
                        class="nav-item"
                        :class="{ active: activeNav === 'advisory' }"
                        @click.prevent="handleNavClick('advisory')"
                    >
                        <Users class="nav-icon" :size="20" />
                        <span class="nav-text">Advisory Class</span>
                    </a>
                    <a
                        href="#"
                        class="nav-item"
                        :class="{ active: activeNav === 'subjects' }"
                        @click.prevent="handleNavClick('subjects')"
                    >
                        <BookOpen class="nav-icon" :size="20" />
                        <span class="nav-text">Subjects Handled</span>
                    </a>
                </div>

                <div class="nav-group">
                    <span class="nav-group-label">Records</span>
                    <a
                        href="#"
                        class="nav-item"
                        :class="{ active: activeNav === 'grades' }"
                        @click.prevent="handleNavClick('grades')"
                    >
                        <ClipboardList class="nav-icon" :size="20" />
                        <span class="nav-text">Student Grades</span>
                    </a>
                    <a
                        href="#"
                        class="nav-item"
                        :class="{ active: activeNav === 'school-forms' }"
                        @click.prevent="handleNavClick('school-forms')"
                    >
                        <FileText class="nav-icon" :size="20" />
                        <span class="nav-text">School Forms</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button @click="logout" class="logout-btn">
                    <LogOut class="nav-icon" :size="20" />
                    <span class="nav-text">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div
            class="mobile-overlay"
            :class="{ active: isMobileMenuOpen }"
            @click="isMobileMenuOpen = false"
        ></div>

        <!-- Main Content -->
        <div class="main-wrapper">
            <header class="dashboard-header">
                <div class="header-left">
                    <button
                        class="mobile-menu-btn"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                    >
                        <Menu :size="24" />
                    </button>
                    <div class="header-content">
                        <h1>{{ getPageTitle }}</h1>
                    </div>
                </div>
                <div class="user-info">
                    <div class="user-meta">
                        <span class="user-role">Teacher</span>
                        <span class="user-name"
                            >{{ user.first_name }} {{ user.last_name }}</span
                        >
                    </div>
                    <div class="user-avatar">
                        <img
                            v-if="profilePhotoUrl"
                            :src="profilePhotoUrl"
                            alt="Profile"
                            class="avatar-img"
                        />
                        <span v-else>{{ userInitials }}</span>
                    </div>
                </div>
            </header>

            <main class="dashboard-main">
                <!-- Dashboard Overview Section -->
                <div
                    v-if="activeNav === 'dashboard'"
                    class="content-section gov-dashboard"
                >
                    <div class="gov-pagehead">
                        <p class="gov-kicker">
                            Tambo National High School — Buhi, Camarines Sur
                        </p>
                        <div class="gov-pagehead-row">
                            <h2>Teacher Dashboard</h2>
                            <span class="gov-sy">School Year 2025-2026</span>
                        </div>
                    </div>

                    <div class="gov-section">
                        <h3 class="gov-section-title">Assignment Summary</h3>
                        <div class="gov-stat-row">
                            <div class="gov-stat-box">
                                <div class="gov-stat-label">
                                    Advisory Students
                                </div>
                                <div class="gov-stat-value">
                                    {{ advisoryStudentsCount }}
                                </div>
                            </div>
                            <div class="gov-stat-box">
                                <div class="gov-stat-label">
                                    Assigned Subjects
                                </div>
                                <div class="gov-stat-value">
                                    {{ assignedSubjectsCount }}
                                </div>
                            </div>
                            <div class="gov-stat-box">
                                <div class="gov-stat-label">
                                    Sections Handled
                                </div>
                                <div class="gov-stat-value">
                                    {{ assignedSectionsCount }}
                                </div>
                            </div>
                            <div class="gov-stat-box">
                                <div class="gov-stat-label">Pending Grades</div>
                                <div class="gov-stat-value">
                                    {{ pendingGradesCount }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="gov-section">
                        <h3 class="gov-section-title">Go to</h3>
                        <div class="gov-link-row">
                            <button
                                type="button"
                                class="gov-link-box"
                                @click="handleNavClick('advisory')"
                            >
                                Advisory Class
                            </button>
                            <button
                                type="button"
                                class="gov-link-box"
                                @click="handleNavClick('subjects')"
                            >
                                Subjects Handled
                            </button>
                            <button
                                type="button"
                                class="gov-link-box"
                                @click="handleNavClick('grades')"
                            >
                                Student Grades
                            </button>
                            <button
                                type="button"
                                class="gov-link-box"
                                @click="handleNavClick('school-forms')"
                            >
                                School Forms
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Advisory Class Section -->
                <div v-if="activeNav === 'advisory'" class="content-section">
                    <!-- Section Header Card -->
                    <div class="advisory-header-card" v-if="advisorySection">
                        <div class="advisory-header-left">
                            <div class="advisory-icon">
                                <GraduationCap :size="28" />
                            </div>
                            <div class="advisory-details">
                                <h2>{{ advisorySection.name }}</h2>
                                <span class="year-level-text">
                                    {{ advisorySection.year_level?.name }}
                                </span>
                            </div>
                        </div>
                        <div class="advisory-header-right">
                            <div class="school-year-display">
                                <Calendar :size="18" />
                                <span class="sy-value">S.Y. 2025-2026</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="advisorySection" class="advisory-content">
                        <!-- Advisory Stats Cards -->
                        <div class="advisory-stats-grid">
                            <div class="advisory-stat-card total">
                                <div class="stat-card-icon">
                                    <Users :size="22" />
                                </div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value">{{
                                        advisoryStudents.length
                                    }}</span>
                                    <span class="stat-card-label"
                                        >Total Students</span
                                    >
                                </div>
                            </div>
                            <div class="advisory-stat-card male">
                                <div class="stat-card-icon">
                                    <User :size="22" />
                                </div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value">{{
                                        maleStudentsCount
                                    }}</span>
                                    <span class="stat-card-label">Male</span>
                                </div>
                            </div>
                            <div class="advisory-stat-card female">
                                <div class="stat-card-icon">
                                    <User :size="22" />
                                </div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value">{{
                                        femaleStudentsCount
                                    }}</span>
                                    <span class="stat-card-label">Female</span>
                                </div>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="data-table-container">
                            <div class="table-header-bar">
                                <h3><Users :size="18" /> Class Roster</h3>
                                <div class="table-controls">
                                    <div class="search-box">
                                        <Search :size="16" />
                                        <input
                                            v-model="advisorySearch"
                                            type="text"
                                            placeholder="Search students..."
                                            class="search-input"
                                        />
                                    </div>
                                    <select
                                        v-model="advisoryGenderFilter"
                                        class="filter-select"
                                    >
                                        <option value="all">All Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <table class="data-table advisory-table">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>LRN</th>
                                        <th>Gender</th>
                                        <th>Contact Info</th>
                                        <th>Guardian</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="student in filteredAdvisoryStudents"
                                        :key="student.id"
                                        class="student-row"
                                    >
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-sm">
                                                    <img
                                                        v-if="
                                                            student.profile_photo
                                                        "
                                                        :src="`/storage/${student.profile_photo}`"
                                                        alt="Profile"
                                                        class="avatar-img"
                                                    />
                                                    <span v-else>{{
                                                        getInitials(student)
                                                    }}</span>
                                                </div>
                                                <div class="user-info-cell">
                                                    <span
                                                        class="user-name-cell"
                                                    >
                                                        {{ student.last_name }},
                                                        {{ student.first_name }}
                                                        {{
                                                            student.middle_name
                                                                ? student.middle_name.charAt(
                                                                      0,
                                                                  ) + "."
                                                                : ""
                                                        }}
                                                        {{
                                                            student.suffix || ""
                                                        }}
                                                    </span>
                                                    <span class="user-meta">{{
                                                        student.email
                                                    }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="lrn-badge">{{
                                                student.lrn || "N/A"
                                            }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="gender-badge"
                                                :class="
                                                    student.gender?.toLowerCase()
                                                "
                                            >
                                                {{ student.gender || "N/A" }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div
                                                    class="contact-item"
                                                    v-if="student.phone_no"
                                                >
                                                    <Phone :size="12" />
                                                    <span>{{
                                                        student.phone_no
                                                    }}</span>
                                                </div>
                                                <div
                                                    class="contact-item"
                                                    v-if="student.municipality"
                                                >
                                                    <MapPin :size="12" />
                                                    <span
                                                        >{{
                                                            student.municipality
                                                        }},
                                                        {{
                                                            student.province
                                                        }}</span
                                                    >
                                                </div>
                                                <span
                                                    v-if="
                                                        !student.phone_no &&
                                                        !student.municipality
                                                    "
                                                    class="text-muted"
                                                    >No contact info</span
                                                >
                                            </div>
                                        </td>
                                        <td>
                                            <div class="guardian-info-compact">
                                                <div
                                                    class="guardian-name"
                                                    v-if="
                                                        student.guardian_full_name
                                                    "
                                                >
                                                    {{
                                                        student.guardian_full_name
                                                    }}
                                                </div>
                                                <div
                                                    class="guardian-contact"
                                                    v-if="
                                                        student.guardian_contact_no
                                                    "
                                                >
                                                    <Phone :size="11" />
                                                    {{
                                                        student.guardian_contact_no
                                                    }}
                                                </div>
                                                <span
                                                    v-if="
                                                        !student.guardian_full_name
                                                    "
                                                    class="text-muted"
                                                    >N/A</span
                                                >
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                class="icon-action-btn view"
                                                @click="
                                                    openStudentModal(student)
                                                "
                                                title="View Full Details"
                                            >
                                                <Eye :size="18" />
                                            </button>
                                        </td>
                                    </tr>
                                    <tr
                                        v-if="
                                            filteredAdvisoryStudents.length ===
                                            0
                                        "
                                    >
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-state-content">
                                                <Users :size="48" />
                                                <p
                                                    v-if="
                                                        advisoryStudents.length ===
                                                        0
                                                    "
                                                >
                                                    No students in advisory
                                                    class
                                                </p>
                                                <p v-else>
                                                    No students match your
                                                    search
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else class="empty-advisory">
                        <div class="empty-notice-bar">Notice</div>
                        <div class="empty-advisory-content">
                            <p>
                                <strong>No advisory class assigned.</strong>
                            </p>
                            <p>
                                You don't have an advisory class assigned yet.
                                Please contact the school administrator.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- School Forms Section -->
                <div
                    v-if="activeNav === 'school-forms'"
                    class="content-section"
                >
                    <SchoolFormsHub
                        viewer="teacher"
                        base-path="/teacher"
                        :school-year="
                            currentSchoolYear ||
                            advisorySection?.school_year ||
                            ''
                        "
                        :current-term="currentTerm"
                        :year-levels="schoolFormYearLevels"
                        :sections="schoolFormSections"
                        :students="schoolFormStudents"
                    />
                </div>

                <!-- Subjects Handled Section -->
                <div v-if="activeNav === 'subjects'" class="content-section">
                    <div class="subjects-header-card">
                        <div class="subjects-header-left">
                            <div class="subjects-icon">
                                <BookOpen :size="28" />
                            </div>
                            <div class="subjects-details">
                                <h2>Subjects Handled</h2>
                                <span class="subjects-count-text">
                                    {{ teacherSubjects.length }} subject{{
                                        teacherSubjects.length !== 1 ? "s" : ""
                                    }}
                                    assigned
                                </span>
                            </div>
                        </div>
                        <div class="subjects-header-right">
                            <div class="school-year-display">
                                <Calendar :size="18" />
                                <span class="sy-value">S.Y. 2025-2026</span>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="teacherSubjects.length > 0"
                        class="subjects-grid"
                    >
                        <div
                            v-for="subject in teacherSubjects"
                            :key="subject.id"
                            class="subject-card"
                        >
                            <div class="subject-card-header">
                                <div class="subject-icon-wrapper">
                                    <BookOpen :size="24" />
                                </div>
                                <div class="subject-info">
                                    <h3 class="subject-name">
                                        {{ subject.name }}
                                    </h3>
                                    <span
                                        class="subject-code"
                                        v-if="subject.code"
                                        >{{ subject.code }}</span
                                    >
                                </div>
                            </div>
                            <div class="subject-card-body">
                                <div
                                    class="subject-detail"
                                    v-if="subject.description"
                                >
                                    <span class="detail-label"
                                        >Description</span
                                    >
                                    <p class="detail-value">
                                        {{ subject.description }}
                                    </p>
                                </div>
                                <div
                                    class="subject-sections"
                                    v-if="
                                        getSubjectSections(subject.id).length >
                                        0
                                    "
                                >
                                    <span class="detail-label"
                                        >Assigned Sections</span
                                    >
                                    <div class="section-tags">
                                        <span
                                            v-for="section in getSubjectSections(
                                                subject.id,
                                            )"
                                            :key="section.id"
                                            class="section-tag"
                                        >
                                            {{ section.name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="subject-card-actions">
                                    <button
                                        class="view-students-btn"
                                        @click="
                                            openSubjectStudentsModal(subject)
                                        "
                                    >
                                        <Users :size="16" />
                                        <span>View Students</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="empty-subjects">
                        <div class="empty-notice-bar">Notice</div>
                        <div class="empty-subjects-content">
                            <p>
                                <strong>No subjects assigned.</strong>
                            </p>
                            <p>
                                You don't have any subjects assigned yet. Please
                                contact the school administrator.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Student Grades Section -->
                <div v-if="activeNav === 'grades'" class="content-section">
                    <div class="gov-pagehead">
                        <p class="gov-kicker">
                            Tambo National High School — Teacher Portal
                        </p>
                        <div class="gov-pagehead-row">
                            <h2>Student Grades</h2>
                        </div>
                    </div>

                    <div class="management-header">
                        <div class="search-filter-container">
                            <div class="search-box">
                                <Search :size="18" class="search-icon" />
                                <input
                                    v-model="gradeSearch"
                                    type="text"
                                    placeholder="Search students..."
                                    class="search-input"
                                />
                            </div>
                            <select
                                v-model="selectedSubjectFilter"
                                class="filter-select"
                            >
                                <option value="all">All Subjects</option>
                                <option
                                    v-for="subject in teacherSubjects"
                                    :key="subject.id"
                                    :value="subject.id"
                                >
                                    {{ subject.name }}
                                </option>
                            </select>

                            <select
                                v-model="selectedSectionFilter"
                                class="filter-select"
                            >
                                <option value="all">All Sections</option>
                                <option
                                    v-for="section in teacherSections"
                                    :key="section.id"
                                    :value="section.id"
                                >
                                    {{ section.name }}
                                </option>
                            </select>

                            <!-- Unfinished Grades Filter Button -->
                            <button
                                type="button"
                                class="unfinished-filter-btn"
                                :class="{ active: showUnfinishedOnly }"
                                @click="
                                    showUnfinishedOnly = !showUnfinishedOnly
                                "
                                title="Show only unfinished grades"
                            >
                                <ClipboardList :size="18" />
                                <span>{{
                                    showUnfinishedOnly
                                        ? "Show All"
                                        : "Unfinished"
                                }}</span>
                            </button>

                            <!-- Print Grades Dropdown -->
                            <div class="print-dropdown-container">
                                <button
                                    type="button"
                                    class="print-grades-btn"
                                    @click="
                                        showPrintDropdown = !showPrintDropdown
                                    "
                                    title="Print grades options"
                                >
                                    <Printer :size="18" />
                                    <span>Print Grades</span>
                                    <ChevronDown
                                        :size="16"
                                        :class="{
                                            'rotate-180': showPrintDropdown,
                                        }"
                                    />
                                </button>

                                <div
                                    v-if="showPrintDropdown"
                                    class="print-dropdown-menu"
                                >
                                    <div class="print-dropdown-header">
                                        <span>Select Sections to Print</span>
                                        <button
                                            type="button"
                                            class="select-all-btn"
                                            @click="toggleSelectAllSections"
                                        >
                                            {{
                                                selectedPrintSections.length ===
                                                teacherSections.length
                                                    ? "Deselect All"
                                                    : "Select All"
                                            }}
                                        </button>
                                    </div>
                                    <div class="print-section-list">
                                        <label
                                            v-for="section in teacherSections"
                                            :key="section.id"
                                            class="print-section-item"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="section.id"
                                                v-model="selectedPrintSections"
                                                class="section-checkbox"
                                            />
                                            <span class="checkmark">
                                                <Check
                                                    v-if="
                                                        selectedPrintSections.includes(
                                                            section.id,
                                                        )
                                                    "
                                                    :size="14"
                                                />
                                            </span>
                                            <span class="section-label">{{
                                                section.name
                                            }}</span>
                                            <span
                                                class="section-year"
                                                v-if="section.year_level"
                                                >{{
                                                    section.year_level.name
                                                }}</span
                                            >
                                        </label>
                                    </div>
                                    <div class="print-dropdown-footer">
                                        <button
                                            type="button"
                                            class="print-cancel-btn"
                                            @click="showPrintDropdown = false"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            class="print-confirm-btn"
                                            @click="printSelectedSections"
                                            :disabled="
                                                selectedPrintSections.length ===
                                                0
                                            "
                                        >
                                            <Printer :size="16" />
                                            Print
                                            {{ selectedPrintSections.length }}
                                            Section{{
                                                selectedPrintSections.length !==
                                                1
                                                    ? "s"
                                                    : ""
                                            }}
                                        </button>
                                    </div>
                                </div>
                                <div
                                    v-if="showPrintDropdown"
                                    class="print-dropdown-overlay"
                                    @click="showPrintDropdown = false"
                                ></div>
                            </div>

                            <!-- Voice Mode Master Toggle -->
                            <button
                                type="button"
                                class="voice-mode-toggle"
                                :class="{ active: voiceModeEnabled }"
                                @click="toggleVoiceModeGlobal"
                                title="Toggle voice input mode (F2)"
                            >
                                <Mic v-if="!voiceModeEnabled" :size="18" />
                                <MicOff v-else :size="18" />
                                <span>{{
                                    voiceModeEnabled ? "Voice ON" : "Voice OFF"
                                }}</span>
                                <kbd class="shortcut-key">F2</kbd>
                            </button>
                        </div>
                    </div>

                    <!-- Table Voice Status Bar -->
                    <div
                        v-if="voiceModeEnabled && !showGradeModal"
                        class="table-voice-bar"
                    >
                        <div class="table-voice-status">
                            <div class="voice-indicator">
                                <span class="pulse-dot"></span>
                                <Mic :size="18" class="mic-icon-active" />
                            </div>
                            <span class="table-voice-text">{{
                                tableVoiceStatus
                            }}</span>
                        </div>
                        <div
                            v-if="voiceTranscript"
                            class="table-voice-transcript"
                        >
                            "{{ voiceTranscript }}"
                        </div>
                        <div class="table-voice-commands">
                            <span class="command-tag">Say student name</span>
                            <span class="command-tag">"Edit"</span>
                            <span class="command-tag">"Cancel"</span>
                        </div>
                    </div>

                    <!-- Grades Table -->
                    <div class="data-table-container">
                        <div class="grades-table-bar">Grade Records</div>
                        <div class="data-table-wrapper">
                            <table
                                class="data-table"
                                :class="{
                                    'voice-active-table': voiceModeEnabled,
                                }"
                            >
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>LRN</th>
                                        <th>Section</th>
                                        <th>Subject</th>
                                        <th>T1</th>
                                        <th>T2</th>
                                        <th>T3</th>
                                        <th>Final</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="grade in filteredGrades"
                                        :key="`${grade.student?.id}-${grade.subject?.id}`"
                                        :id="`grade-row-${grade.student?.id}-${grade.subject?.id}`"
                                        :class="{
                                            'voice-focused-row':
                                                focusedGradeRow &&
                                                focusedGradeRow.student?.id ===
                                                    grade.student?.id &&
                                                focusedGradeRow.subject?.id ===
                                                    grade.subject?.id,
                                        }"
                                        @click="
                                            voiceModeEnabled
                                                ? (focusedGradeRow = grade)
                                                : null
                                        "
                                    >
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-sm">
                                                    {{
                                                        getInitials(
                                                            grade.student,
                                                        )
                                                    }}
                                                </div>
                                                <div class="user-info-cell">
                                                    <span
                                                        class="user-name-cell"
                                                    >
                                                        {{
                                                            grade.student
                                                                ?.last_name
                                                        }},
                                                        {{
                                                            grade.student
                                                                ?.first_name
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="lrn-badge">{{
                                                grade.student?.lrn || "N/A"
                                            }}</span>
                                        </td>
                                        <td>
                                            {{ grade.section?.name || "N/A" }}
                                        </td>
                                        <td>
                                            {{ grade.subject?.name || "N/A" }}
                                        </td>
                                        <td>
                                            <span
                                                class="grade-cell"
                                                :class="getGradeClass(grade.term_1)"
                                            >
                                                {{ grade.term_1 || "-" }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="grade-cell"
                                                :class="getGradeClass(grade.term_2)"
                                            >
                                                {{ grade.term_2 || "-" }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="grade-cell"
                                                :class="getGradeClass(grade.term_3)"
                                            >
                                                {{ grade.term_3 || "-" }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="grade-cell final"
                                                :class="
                                                    getGradeClass(
                                                        grade.final_grade,
                                                    )
                                                "
                                            >
                                                {{ grade.final_grade || "-" }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button
                                                    class="action-btn edit"
                                                    @click="
                                                        openGradeModal(grade)
                                                    "
                                                    title="Edit Grades"
                                                >
                                                    <Pencil :size="16" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredGrades.length === 0">
                                        <td colspan="9" class="empty-state">
                                            <div class="empty-state-content">
                                                <p>No grades found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Grade Edit Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showGradeModal"
                    class="modal-overlay"
                    @click.self="closeGradeModal"
                >
                    <div class="modal-container grade-modal">
                        <!-- Professional Header with Gradient -->
                        <div class="modal-header grade-modal-header">
                            <div class="grade-header-content">
                                <div class="grade-header-icon">
                                    <ClipboardList :size="24" />
                                </div>
                                <div class="grade-header-text">
                                    <h3>Grade Entry</h3>
                                    <p>
                                        Enter trimester grades for this student
                                    </p>
                                </div>
                            </div>
                            <button class="close-btn" @click="closeGradeModal">
                                <X :size="20" />
                            </button>
                        </div>

                        <div class="modal-body grade-modal-body">
                            <div class="grade-modal-layout">
                                <!-- Left Column: Student Info & Voice Control -->
                                <div class="grade-left-column">
                                    <!-- Student Info Card -->
                                    <div class="student-info-card">
                                        <div class="student-avatar-section">
                                            <div class="student-avatar-large">
                                                <img
                                                    v-if="
                                                        selectedGrade?.student
                                                            ?.profile_photo
                                                    "
                                                    :src="`/storage/${selectedGrade.student.profile_photo}`"
                                                    alt="Profile"
                                                    class="avatar-img"
                                                />
                                                <span
                                                    v-else
                                                    class="avatar-initials"
                                                >
                                                    {{
                                                        selectedGrade?.student?.first_name?.charAt(
                                                            0,
                                                        )
                                                    }}{{
                                                        selectedGrade?.student?.last_name?.charAt(
                                                            0,
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="student-details-section">
                                            <h4 class="student-full-name">
                                                {{
                                                    selectedGrade?.student
                                                        ?.last_name
                                                }},
                                                {{
                                                    selectedGrade?.student
                                                        ?.first_name
                                                }}
                                            </h4>
                                            <div class="student-meta-badges">
                                                <span
                                                    class="meta-badge subject-badge"
                                                >
                                                    <BookOpen :size="14" />
                                                    {{
                                                        selectedGrade?.subject
                                                            ?.name
                                                    }}
                                                </span>
                                                <span
                                                    class="meta-badge section-badge"
                                                    v-if="
                                                        selectedGrade?.section
                                                    "
                                                >
                                                    <Layers :size="14" />
                                                    {{
                                                        selectedGrade?.section
                                                            ?.name
                                                    }}
                                                </span>
                                                <span
                                                    class="meta-badge lrn-meta-badge"
                                                    v-if="
                                                        selectedGrade?.student
                                                            ?.lrn
                                                    "
                                                >
                                                    <Hash :size="14" />
                                                    {{
                                                        selectedGrade?.student
                                                            ?.lrn
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Voice Control Section -->
                                    <div
                                        v-if="voiceModeEnabled"
                                        class="voice-control-section"
                                    >
                                        <div class="voice-mode-indicator">
                                            <Mic
                                                :size="20"
                                                class="mic-icon-active"
                                            />
                                            <span class="voice-mode-label"
                                                >Voice Mode Active</span
                                            >
                                            <button
                                                type="button"
                                                class="voice-disable-btn"
                                                @click="toggleVoiceRecognition"
                                            >
                                                <X :size="16" />
                                                Turn Off
                                            </button>
                                        </div>
                                        <div
                                            v-if="isVoiceActive"
                                            class="voice-status-container"
                                        >
                                            <div class="voice-indicator">
                                                <span class="pulse-dot"></span>
                                                <span class="voice-label"
                                                    >Listening...</span
                                                >
                                            </div>
                                            <div class="current-quarter-badge">
                                                T{{ currentVoiceQuarter }}
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="voice-control-section voice-off"
                                    >
                                        <button
                                            type="button"
                                            class="voice-toggle-btn"
                                            @click="toggleVoiceRecognition"
                                        >
                                            <Mic :size="20" />
                                            <span>Enable Voice Input</span>
                                        </button>
                                    </div>

                                    <!-- Voice Status Bar -->
                                    <div
                                        v-if="isVoiceActive"
                                        class="voice-status-bar"
                                    >
                                        <div class="voice-status-text">
                                            {{ voiceStatus }}
                                        </div>
                                        <div
                                            v-if="voiceTranscript"
                                            class="voice-transcript"
                                        >
                                            "{{ voiceTranscript }}"
                                        </div>
                                        <div class="voice-commands">
                                            <span class="command-hint"
                                                >Commands: "Term 1-3" |
                                                "Next" | "Back" | "Clear" |
                                                "Save"</span>
                                            >
                                        </div>
                                    </div>

                                    <!-- Final Grade Display -->
                                    <div class="final-grade-section">
                                        <div
                                            class="final-grade-card"
                                            :class="
                                                getGradeClass(
                                                    computedFinalGrade,
                                                )
                                            "
                                        >
                                            <div class="final-grade-label">
                                                Final Grade
                                            </div>
                                            <div class="final-grade-value">
                                                {{ computedFinalGrade || "--" }}
                                            </div>
                                            <div
                                                class="final-grade-status"
                                                v-if="computedFinalGrade"
                                            >
                                                {{
                                                    getGradeLabel(
                                                        computedFinalGrade,
                                                    )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Trimester Grades -->
                                <div class="grade-right-column">
                                    <div class="grades-section">
                                        <div class="section-title">
                                            <span class="title-text"
                                                >Trimester Grades</span
                                            >
                                            <span class="title-hint"
                                                >Enter grades from 60-100</span
                                            >
                                        </div>

                                        <div
                                            class="quarter-grades-grid-vertical"
                                        >
                                            <div
                                                class="quarter-card-horizontal"
                                                :class="{
                                                    active:
                                                        voiceModeEnabled &&
                                                        currentVoiceQuarter ===
                                                            1,
                                                    'has-grade': gradeForm.term_1,
                                                }"
                                            >
                                                <div class="quarter-info">
                                                    <span class="quarter-label"
                                                        >T1</span
                                                    >
                                                    <span class="quarter-title"
                                                        >1st Term</span
                                                    >
                                                </div>
                                                <div
                                                    class="quarter-input-wrapper"
                                                >
                                                    <input
                                                        id="voice-term-1-input"
                                                        v-model="gradeForm.term_1"
                                                        type="number"
                                                        min="60"
                                                        max="100"
                                                        step="0.01"
                                                        placeholder="--"
                                                        class="quarter-input"
                                                    />
                                                </div>
                                                <div
                                                    class="grade-indicator"
                                                    :class="
                                                        getGradeClass(
                                                            gradeForm.term_1,
                                                        )
                                                    "
                                                    v-if="gradeForm.term_1"
                                                >
                                                    {{
                                                        getGradeLabel(
                                                            gradeForm.term_1,
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="grade-indicator empty"
                                                    v-else
                                                >
                                                    Not Set
                                                </div>
                                            </div>

                                            <div
                                                class="quarter-card-horizontal"
                                                :class="{
                                                    active:
                                                        voiceModeEnabled &&
                                                        currentVoiceQuarter ===
                                                            2,
                                                    'has-grade': gradeForm.term_2,
                                                }"
                                            >
                                                <div class="quarter-info">
                                                    <span class="quarter-label"
                                                        >T2</span
                                                    >
                                                    <span class="quarter-title"
                                                        >2nd Term</span
                                                    >
                                                </div>
                                                <div
                                                    class="quarter-input-wrapper"
                                                >
                                                    <input
                                                        id="voice-term-2-input"
                                                        v-model="gradeForm.term_2"
                                                        type="number"
                                                        min="60"
                                                        max="100"
                                                        step="0.01"
                                                        placeholder="--"
                                                        class="quarter-input"
                                                    />
                                                </div>
                                                <div
                                                    class="grade-indicator"
                                                    :class="
                                                        getGradeClass(
                                                            gradeForm.term_2,
                                                        )
                                                    "
                                                    v-if="gradeForm.term_2"
                                                >
                                                    {{
                                                        getGradeLabel(
                                                            gradeForm.term_2,
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="grade-indicator empty"
                                                    v-else
                                                >
                                                    Not Set
                                                </div>
                                            </div>

                                            <div
                                                class="quarter-card-horizontal"
                                                :class="{
                                                    active:
                                                        voiceModeEnabled &&
                                                        currentVoiceQuarter ===
                                                            3,
                                                    'has-grade': gradeForm.term_3,
                                                }"
                                            >
                                                <div class="quarter-info">
                                                    <span class="quarter-label"
                                                        >T3</span
                                                    >
                                                    <span class="quarter-title"
                                                        >3rd Term</span
                                                    >
                                                </div>
                                                <div
                                                    class="quarter-input-wrapper"
                                                >
                                                    <input
                                                        id="voice-term-3-input"
                                                        v-model="gradeForm.term_3"
                                                        type="number"
                                                        min="60"
                                                        max="100"
                                                        step="0.01"
                                                        placeholder="--"
                                                        class="quarter-input"
                                                    />
                                                </div>
                                                <div
                                                    class="grade-indicator"
                                                    :class="
                                                        getGradeClass(
                                                            gradeForm.term_3,
                                                        )
                                                    "
                                                    v-if="gradeForm.term_3"
                                                >
                                                    {{
                                                        getGradeLabel(
                                                            gradeForm.term_3,
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="grade-indicator empty"
                                                    v-else
                                                >
                                                    Not Set
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Saving Indicator -->
                                        <div
                                            v-if="isSubmitting"
                                            class="saving-indicator"
                                        >
                                            <Loader2 :size="18" class="spin" />
                                            <span>Saving grades...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="!voiceModeEnabled"
                            class="modal-footer grade-modal-footer"
                        >
                            <button
                                class="btn-secondary"
                                @click="closeGradeModal"
                            >
                                <X :size="18" />
                                Cancel
                            </button>
                            <button
                                class="btn-primary btn-save-grades"
                                @click="submitGrades"
                                :disabled="isSubmitting"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                <span v-else>💾</span>
                                {{ isSubmitting ? "Saving..." : "Save Grades" }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Student View Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showStudentModal"
                    class="modal-overlay student-modal-overlay"
                    @click.self="closeStudentModal"
                >
                    <div class="modal-container large student-modal">
                        <div class="modal-header gradient-header">
                            <div class="header-content">
                                <User :size="22" class="header-icon" />
                                <h3>Student Information</h3>
                            </div>
                            <button
                                class="close-btn"
                                @click="closeStudentModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div
                            class="modal-body student-modal-body"
                            v-if="selectedStudent"
                        >
                            <!-- Profile Header Card -->
                            <div class="student-profile-card">
                                <div class="profile-avatar-large">
                                    <img
                                        v-if="selectedStudent.profile_photo"
                                        :src="`/storage/${selectedStudent.profile_photo}`"
                                        alt="Profile"
                                        class="avatar-image"
                                    />
                                    <span v-else class="avatar-initials">{{
                                        getInitials(selectedStudent)
                                    }}</span>
                                </div>
                                <div class="profile-main-info">
                                    <h4 class="student-full-name">
                                        {{ selectedStudent.first_name }}
                                        {{ selectedStudent.middle_name || "" }}
                                        {{ selectedStudent.last_name }}
                                        {{ selectedStudent.suffix || "" }}
                                    </h4>
                                    <div class="student-badges">
                                        <span class="lrn-badge large">
                                            <Hash :size="14" />
                                            LRN:
                                            {{ selectedStudent.lrn || "N/A" }}
                                        </span>
                                        <span
                                            class="gender-badge large"
                                            :class="
                                                selectedStudent.gender?.toLowerCase()
                                            "
                                        >
                                            {{
                                                selectedStudent.gender || "N/A"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Information Sections Grid -->
                            <div class="info-sections-grid">
                                <div class="info-section">
                                    <div class="section-header">
                                        <div class="section-icon personal">
                                            <User :size="18" />
                                        </div>
                                        <h5>Personal Information</h5>
                                    </div>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <label
                                                ><Mail :size="13" />
                                                Email</label
                                            >
                                            <span>{{
                                                selectedStudent.email ||
                                                "Not provided"
                                            }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label
                                                ><Phone :size="13" />
                                                Phone</label
                                            >
                                            <span>{{
                                                selectedStudent.phone_no ||
                                                "Not provided"
                                            }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label
                                                ><Calendar :size="13" /> Date of
                                                Birth</label
                                            >
                                            <span>{{
                                                formatDate(
                                                    selectedStudent.date_of_birth,
                                                ) || "Not provided"
                                            }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label
                                                ><GraduationCap :size="13" />
                                                Previous GWA</label
                                            >
                                            <span class="gwa-value">{{
                                                selectedStudent.previous_gwa ||
                                                "N/A"
                                            }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-section">
                                    <div class="section-header">
                                        <div class="section-icon address">
                                            <MapPin :size="18" />
                                        </div>
                                        <h5>Address</h5>
                                    </div>
                                    <div class="info-grid single-column">
                                        <div class="info-item full-width">
                                            <label
                                                ><MapPin :size="13" /> Complete
                                                Address</label
                                            >
                                            <span class="address-text">
                                                {{
                                                    [
                                                        selectedStudent.barangay,
                                                        selectedStudent.municipality,
                                                        selectedStudent.province,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(", ") ||
                                                    "No address on record"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-section">
                                    <div class="section-header">
                                        <div class="section-icon guardian">
                                            <Users :size="18" />
                                        </div>
                                        <h5>Guardian Information</h5>
                                    </div>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <label
                                                ><User :size="13" /> Guardian
                                                Name</label
                                            >
                                            <span>{{
                                                selectedStudent.guardian_full_name ||
                                                "Not provided"
                                            }}</span>
                                        </div>
                                        <div class="info-item">
                                            <label
                                                ><Phone :size="13" /> Guardian
                                                Contact</label
                                            >
                                            <span>{{
                                                selectedStudent.guardian_contact_no ||
                                                "Not provided"
                                            }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                class="btn-secondary"
                                @click="closeStudentModal"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Subject Students Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showSubjectStudentsModal"
                    class="modal-overlay"
                    @click.self="closeSubjectStudentsModal"
                >
                    <div class="modal-container large subject-students-modal">
                        <div class="modal-header gradient-header">
                            <div class="header-content">
                                <BookOpen :size="22" class="header-icon" />
                                <div class="header-text">
                                    <h3>{{ selectedSubjectForView?.name }}</h3>
                                    <span class="header-subtitle"
                                        >Enrolled Students</span
                                    >
                                </div>
                            </div>
                            <button
                                class="close-btn"
                                @click="closeSubjectStudentsModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body subject-students-body">
                            <!-- Search Box -->
                            <div class="modal-search-bar">
                                <div class="search-box-modal">
                                    <Search :size="16" />
                                    <input
                                        v-model="subjectStudentsSearch"
                                        type="text"
                                        placeholder="Search students..."
                                        class="search-input"
                                    />
                                </div>
                                <span class="students-count-badge">
                                    {{ filteredSubjectStudents.length }}
                                    student{{
                                        filteredSubjectStudents.length !== 1
                                            ? "s"
                                            : ""
                                    }}
                                </span>
                            </div>

                            <!-- Students List -->
                            <div
                                class="subject-students-list"
                                v-if="filteredSubjectStudents.length > 0"
                            >
                                <div
                                    v-for="student in filteredSubjectStudents"
                                    :key="student.id"
                                    class="subject-student-card"
                                >
                                    <div class="student-card-avatar">
                                        <img
                                            v-if="student.profile_photo"
                                            :src="`/storage/${student.profile_photo}`"
                                            alt="Profile"
                                            class="avatar-img"
                                        />
                                        <span v-else class="avatar-initials">{{
                                            getInitials(student)
                                        }}</span>
                                    </div>
                                    <div class="student-card-info">
                                        <h4 class="student-card-name">
                                            {{ student.last_name }},
                                            {{ student.first_name }}
                                            {{
                                                student.middle_name
                                                    ? student.middle_name.charAt(
                                                          0,
                                                      ) + "."
                                                    : ""
                                            }}
                                        </h4>
                                        <div class="student-card-meta">
                                            <span class="meta-item">
                                                <Hash :size="12" />
                                                {{ student.lrn || "N/A" }}
                                            </span>
                                            <span class="meta-item">
                                                <Layers :size="12" />
                                                {{ student.sectionName }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="student-card-actions">
                                        <button
                                            class="icon-action-btn view"
                                            @click="openStudentModal(student)"
                                            title="View Details"
                                        >
                                            <Eye :size="18" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="empty-subject-students">
                                <Users :size="48" />
                                <p v-if="subjectStudentsSearch">
                                    No students match your search
                                </p>
                                <p v-else>
                                    No students enrolled in this subject
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                class="btn-secondary"
                                @click="closeSubjectStudentsModal"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router, Head } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import SchoolFormsHub from "@/Pages/Dashboard/SchoolForms/SchoolFormsHub.vue";
import {
    LayoutDashboard,
    Users,
    ClipboardList,
    BookOpen,
    Layers,
    FileText,
    LogOut,
    Menu,
    Calendar,
    GraduationCap,
    Search,
    Pencil,
    X,
    Loader2,
    Eye,
    User,
    MapPin,
    Phone,
    Mail,
    Hash,
    Mic,
    MicOff,
    Printer,
    ChevronDown,
    Check,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    advisorySection: {
        type: Object,
        default: null,
    },
    advisoryStudents: {
        type: Array,
        default: () => [],
    },
    teacherSubjects: {
        type: Array,
        default: () => [],
    },
    teacherSections: {
        type: Array,
        default: () => [],
    },
    studentGrades: {
        type: Array,
        default: () => [],
    },
    currentSchoolYear: {
        type: String,
        default: "",
    },
    currentTerm: {
        type: [Number, String],
        default: null,
    },
});

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";
const activeNav = ref("dashboard");
const isMobileMenuOpen = ref(false);
const isSubmitting = ref(false);

// Grades State
const gradeSearch = ref("");
const selectedSubjectFilter = ref("all");
const selectedSectionFilter = ref("all");
const showGradeModal = ref(false);
const selectedGrade = ref(null);
const gradeForm = ref({
    term_1: null,
    term_2: null,
    term_3: null,
});

// Voice Recognition State
const voiceModeEnabled = ref(false); // Persistent toggle - stays on across edits
const isVoiceActive = ref(false);
const currentVoiceQuarter = ref(1);
const voiceTranscript = ref("");
const speechRecognition = ref(null);
const voiceStatus = ref("Voice mode off");

// Table Voice Mode State
const tableVoiceActive = ref(false);
const focusedGradeRow = ref(null); // The grade row currently focused by voice
const tableVoiceStatus = ref("Say a student name to select...");

// Advisory Search/Filter State
const advisorySearch = ref("");
const advisoryGenderFilter = ref("all");

// Student Modal State
const showStudentModal = ref(false);
const selectedStudent = ref(null);

// Subject Students Modal State
const showSubjectStudentsModal = ref(false);
const selectedSubjectForView = ref(null);
const subjectStudentsSearch = ref("");

// Print Options State
const showPrintDropdown = ref(false);
const selectedPrintSections = ref([]);

// Unfinished Grades Filter
const showUnfinishedOnly = ref(false);

// Computed Properties
const advisoryStudentsCount = computed(
    () => props.advisoryStudents?.length || 0,
);
const assignedSubjectsCount = computed(
    () => props.teacherSubjects?.length || 0,
);
const assignedSectionsCount = computed(
    () => props.teacherSections?.length || 0,
);
const pendingGradesCount = computed(() => {
    return props.studentGrades?.filter((g) => !g.final_grade).length || 0;
});

const maleStudentsCount = computed(() => {
    return (
        props.advisoryStudents?.filter(
            (s) => s.gender?.toLowerCase() === "male",
        ).length || 0
    );
});

const femaleStudentsCount = computed(() => {
    return (
        props.advisoryStudents?.filter(
            (s) => s.gender?.toLowerCase() === "female",
        ).length || 0
    );
});

const filteredAdvisoryStudents = computed(() => {
    let filtered = [...(props.advisoryStudents || [])];

    if (advisoryGenderFilter.value !== "all") {
        filtered = filtered.filter(
            (s) => s.gender?.toLowerCase() === advisoryGenderFilter.value,
        );
    }

    if (advisorySearch.value) {
        const search = advisorySearch.value.toLowerCase();
        filtered = filtered.filter(
            (s) =>
                s.first_name?.toLowerCase().includes(search) ||
                s.last_name?.toLowerCase().includes(search) ||
                s.lrn?.toLowerCase().includes(search) ||
                s.email?.toLowerCase().includes(search),
        );
    }

    return filtered;
});

const filteredGrades = computed(() => {
    let filtered = [...(props.studentGrades || [])];

    if (selectedSubjectFilter.value !== "all") {
        filtered = filtered.filter(
            (g) => g.subject_id === selectedSubjectFilter.value,
        );
    }

    if (selectedSectionFilter.value !== "all") {
        filtered = filtered.filter(
            (g) => g.section_id === selectedSectionFilter.value,
        );
    }

    // Filter unfinished grades (missing any term or final grade)
    if (showUnfinishedOnly.value) {
        filtered = filtered.filter(
            (g) => !g.term_1 || !g.term_2 || !g.term_3 || !g.final_grade,
        );
    }

    if (gradeSearch.value) {
        const search = gradeSearch.value.toLowerCase();
        filtered = filtered.filter(
            (g) =>
                g.student?.first_name?.toLowerCase().includes(search) ||
                g.student?.last_name?.toLowerCase().includes(search),
        );
    }

    // Sort by subject first, then by section, then by student last name alphabetically
    filtered.sort((a, b) => {
        // First, sort by subject name
        const subjectA = a.subject?.name?.toLowerCase() || "";
        const subjectB = b.subject?.name?.toLowerCase() || "";
        if (subjectA < subjectB) return -1;
        if (subjectA > subjectB) return 1;

        // Then, sort by section name
        const sectionA = a.section?.name?.toLowerCase() || "";
        const sectionB = b.section?.name?.toLowerCase() || "";
        if (sectionA < sectionB) return -1;
        if (sectionA > sectionB) return 1;

        // If same section, sort by student last name
        const lastNameA = a.student?.last_name?.toLowerCase() || "";
        const lastNameB = b.student?.last_name?.toLowerCase() || "";
        if (lastNameA < lastNameB) return -1;
        if (lastNameA > lastNameB) return 1;

        // If same last name, sort by first name
        const firstNameA = a.student?.first_name?.toLowerCase() || "";
        const firstNameB = b.student?.first_name?.toLowerCase() || "";
        if (firstNameA < firstNameB) return -1;
        if (firstNameA > firstNameB) return 1;

        return 0;
    });

    return filtered;
});

const computedFinalGrade = computed(() => {
    const term1 = parseFloat(gradeForm.value.term_1) || 0;
    const term2 = parseFloat(gradeForm.value.term_2) || 0;
    const term3 = parseFloat(gradeForm.value.term_3) || 0;

    if (!term1 && !term2 && !term3) return null;

    const count = [term1, term2, term3].filter((g) => g > 0).length;
    if (count === 0) return null;

    const sum = term1 + term2 + term3;
    return (sum / count).toFixed(2);
});

const schoolFormSections = computed(() => {
    if (!props.advisorySection) {
        return [];
    }

    return [
        {
            id: props.advisorySection.id,
            name: props.advisorySection.name,
            year_level: props.advisorySection.year_level?.name,
            enrolled_count: props.advisoryStudents?.length || 0,
        },
    ];
});

const schoolFormYearLevels = computed(() => {
    const yearLevel = props.advisorySection?.year_level;
    if (!yearLevel) {
        return [];
    }

    return [
        {
            id: yearLevel.id || props.advisorySection.year_level_id,
            name: yearLevel.name,
        },
    ];
});

const schoolFormStudents = computed(() => {
    const yearLevelId =
        props.advisorySection?.year_level_id ||
        props.advisorySection?.year_level?.id;
    const yearLevelName = props.advisorySection?.year_level?.name;

    return (props.advisoryStudents || []).map((student) => ({
        ...student,
        year_level_id: student.year_level_id || yearLevelId,
        year_level: student.year_level || yearLevelName,
    }));
});

const getPageTitle = computed(() => {
    const titles = {
        dashboard: "Dashboard",
        advisory: "Advisory Class",
        grades: "Student Grades",
        "school-forms": "School Forms",
    };
    return titles[activeNav.value] || "Dashboard";
});

const profilePhotoUrl = computed(() => {
    return props.user?.profile_photo_url || null;
});

const userInitials = computed(() => {
    const first = props.user?.first_name?.charAt(0) || "";
    const last = props.user?.last_name?.charAt(0) || "";
    return (first + last).toUpperCase();
});

// Methods
const handleNavClick = (nav) => {
    activeNav.value = nav;
    isMobileMenuOpen.value = false;
};

const getInitials = (user) => {
    const first = user?.first_name?.charAt(0) || "";
    const last = user?.last_name?.charAt(0) || "";
    return (first + last).toUpperCase();
};

const getSubjectSections = (subjectId) => {
    // Filter sections where this subject is taught by this teacher
    return (
        props.teacherSections?.filter((section) => {
            // Check if this section has an assignment for this subject
            return section.subject_teachers?.some(
                (st) =>
                    st.subject_id === subjectId &&
                    st.teacher_id === props.user?.id,
            );
        }) || []
    );
};

const getGradeClass = (grade) => {
    if (!grade) return "";
    const g = parseFloat(grade);
    if (g >= 90) return "excellent";
    if (g >= 85) return "very-good";
    if (g >= 80) return "good";
    if (g >= 75) return "satisfactory";
    return "needs-improvement";
};

const getGradeLabel = (grade) => {
    if (!grade) return "";
    const g = parseFloat(grade);
    if (g >= 90) return "Outstanding";
    if (g >= 85) return "Very Satisfactory";
    if (g >= 80) return "Satisfactory";
    if (g >= 75) return "Fairly Satisfactory";
    return "Did Not Meet";
};

const openGradeModal = (grade) => {
    selectedGrade.value = grade;
    gradeForm.value = {
        term_1: grade.term_1,
        term_2: grade.term_2,
        term_3: grade.term_3,
    };
    showGradeModal.value = true;

    // Stop table voice recognition first
    stopTableVoiceRecognition();
    focusedGradeRow.value = null;

    // Auto-start modal voice recognition if voice mode is enabled
    if (voiceModeEnabled.value) {
        setTimeout(() => {
            startVoiceRecognition();
        }, 100);
    }
};

const closeGradeModal = () => {
    // Pause modal voice recognition but keep mode enabled
    pauseVoiceRecognition();

    showGradeModal.value = false;
    selectedGrade.value = null;
    gradeForm.value = { term_1: null, term_2: null, term_3: null };

    // Restart table voice recognition if voice mode is still enabled
    if (voiceModeEnabled.value) {
        setTimeout(() => {
            startTableVoiceRecognition();
        }, 100);
    }
};

// Voice Recognition Functions
let lastProcessedTranscript = "";

const initVoiceRecognition = () => {
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        toast.error("Voice recognition is not supported in your browser");
        return null;
    }

    const recognition = new SpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = "en-US";
    recognition.maxAlternatives = 3; // More alternatives for better accuracy

    // Track which commands have been processed to prevent duplicates
    let lastProcessedCommand = "";

    recognition.onresult = (event) => {
        const result = event.results[event.results.length - 1];
        const fullTranscript = result[0].transcript.toLowerCase().trim();

        // Get last word and last two words for command processing
        const words = fullTranscript.split(/\s+/);
        const lastWord = words[words.length - 1];
        const lastTwoWords = words.slice(-2).join(" ");

        // Show transcript (last two words for context)
        voiceTranscript.value = `"${words.slice(-2).join(" ")}"`;

        // Create a command key to prevent duplicate processing
        const commandKey = `${lastWord}-${currentVoiceQuarter.value}`;

        // Only process if this exact command hasn't been processed yet
        if (
            commandKey !== lastProcessedCommand &&
            lastTwoWords !== lastProcessedTranscript
        ) {
            const processed = tryProcessCommand(
                lastWord,
                lastTwoWords,
                result.isFinal,
            );
            if (processed) {
                lastProcessedCommand = commandKey;
                lastProcessedTranscript = lastTwoWords;
                // Clear transcript quickly after processing
                setTimeout(() => {
                    voiceTranscript.value = "";
                }, 800);
            }
        }

        // Reset for next command on final result
        if (result.isFinal) {
            lastProcessedTranscript = "";
            lastProcessedCommand = "";
        }
    };

    recognition.onerror = (event) => {
        console.error("Voice recognition error:", event.error);
        if (event.error === "no-speech") {
            // Don't show error, just keep listening
        } else if (event.error === "audio-capture") {
            voiceStatus.value = "No microphone found.";
            stopVoiceRecognition();
        } else if (event.error === "not-allowed") {
            voiceStatus.value = "Microphone access denied.";
            stopVoiceRecognition();
        } else if (event.error === "aborted") {
            // Silently restart
        }
    };

    recognition.onend = () => {
        if (isVoiceActive.value) {
            // Restart immediately without delay
            try {
                recognition.start();
            } catch (e) {
                // Retry after tiny delay if immediate start fails
                setTimeout(() => {
                    try {
                        recognition.start();
                    } catch (e2) {}
                }, 50);
            }
        }
    };

    return recognition;
};

// Helper to show status and auto-clear after 1 second
let statusTimeout = null;
const showStatus = (message) => {
    voiceStatus.value = message;
    if (statusTimeout) clearTimeout(statusTimeout);
    statusTimeout = setTimeout(() => {
        voiceStatus.value = `🎤 T${currentVoiceQuarter.value} - Listening...`;
    }, 800); // Faster status clear
};

// Track last navigation command to prevent double-triggering
let lastNavCommand = "";
let lastNavTime = 0;

// Try to process command - returns true if a command was recognized
// Receives the last word AND last two words for multi-word commands
const tryProcessCommand = (word, twoWords, isFinal) => {
    const now = Date.now();

    // Check for two-word term/trimester commands (quarter kept as alias)
    const quarterTwoWordPatterns = {
        "term 1": 1,
        "term one": 1,
        "term won": 1,
        "term wan": 1,
        "trimester 1": 1,
        "trimester one": 1,
        "quarter 1": 1,
        "quarter one": 1,
        "quarter won": 1,
        "quarter wan": 1,
        "term 2": 2,
        "term two": 2,
        "term to": 2,
        "term too": 2,
        "trimester 2": 2,
        "trimester two": 2,
        "quarter 2": 2,
        "quarter two": 2,
        "quarter to": 2,
        "quarter too": 2,
        "term 3": 3,
        "term three": 3,
        "term tree": 3,
        "term free": 3,
        "trimester 3": 3,
        "trimester three": 3,
        "quarter 3": 3,
        "quarter three": 3,
        "quarter tree": 3,
        "quarter free": 3,
    };

    if (quarterTwoWordPatterns[twoWords]) {
        // Prevent double-trigger within 1 second
        if (lastNavCommand === twoWords && now - lastNavTime < 1000) {
            return false;
        }
        lastNavCommand = twoWords;
        lastNavTime = now;
        currentVoiceQuarter.value = quarterTwoWordPatterns[twoWords];
        showStatus(`→ T${quarterTwoWordPatterns[twoWords]}`);
        focusQuarterInput(quarterTwoWordPatterns[twoWords]);
        return true;
    }

    // Check if word is a grade number (60-100)
    const gradeNumber = parseSpokenNumber(word);
    if (gradeNumber !== null && gradeNumber >= 60 && gradeNumber <= 100) {
        const termKey = `term_${currentVoiceQuarter.value}`;
        gradeForm.value[termKey] = gradeNumber;
        showStatus(`✓ T${currentVoiceQuarter.value} = ${gradeNumber}`);
        // Reset nav tracking when grade is entered
        lastNavCommand = "";
        return true;
    }

    // Navigation: NEXT - move to next term (with debounce)
    const nextWords = ["next", "necks", "text", "nest"];
    if (nextWords.includes(word)) {
        // Prevent double-trigger within 1 second
        if (lastNavCommand === "next" && now - lastNavTime < 1000) {
            return false;
        }
        lastNavCommand = "next";
        lastNavTime = now;

        if (currentVoiceQuarter.value < 3) {
            currentVoiceQuarter.value++;
            showStatus(`→ T${currentVoiceQuarter.value}`);
            focusQuarterInput(currentVoiceQuarter.value);
        } else {
            showStatus("At T3. Say 'save'");
        }
        return true;
    }

    // Navigation: BACK - move to previous term (with debounce)
    const backWords = ["back", "bag", "beck", "bak"];
    if (backWords.includes(word)) {
        // Prevent double-trigger within 1 second
        if (lastNavCommand === "back" && now - lastNavTime < 1000) {
            return false;
        }
        lastNavCommand = "back";
        lastNavTime = now;

        if (currentVoiceQuarter.value > 1) {
            currentVoiceQuarter.value--;
            showStatus(`→ T${currentVoiceQuarter.value}`);
            focusQuarterInput(currentVoiceQuarter.value);
        }
        return true;
    }

    // CLEAR - clear current term
    if (
        word === "clear" ||
        word === "claire" ||
        word === "klir" ||
        word === "kleer"
    ) {
        const termKey = `term_${currentVoiceQuarter.value}`;
        gradeForm.value[termKey] = null;
        showStatus(`✓ Cleared T${currentVoiceQuarter.value}`);
        return true;
    }

    // SAVE - submit grades
    const saveWords = ["save", "safe", "saved", "sabe", "seif", "sayb", "sef"];
    if (saveWords.includes(word)) {
        voiceStatus.value = "💾 Saving...";
        submitGrades();
        return true;
    }

    // Term jump - single word "t1", "q1", etc.
    if (word === "t1" || word === "q1" || word === "queue1") {
        currentVoiceQuarter.value = 1;
        showStatus("→ T1");
        focusQuarterInput(1);
        return true;
    }
    if (word === "t2" || word === "q2" || word === "queue2") {
        currentVoiceQuarter.value = 2;
        showStatus("→ T2");
        focusQuarterInput(2);
        return true;
    }
    if (word === "t3" || word === "q3" || word === "queue3") {
        currentVoiceQuarter.value = 3;
        showStatus("→ T3");
        focusQuarterInput(3);
        return true;
    }
    if (word === "q4" || word === "queue4") {
        currentVoiceQuarter.value = 3;
        showStatus("→ T3");
        focusQuarterInput(3);
        return true;
    }

    return false;
};

const parseSpokenNumber = (transcript) => {
    // Word to number mapping
    const wordNumbers = {
        zero: 0,
        one: 1,
        two: 2,
        three: 3,
        four: 4,
        five: 5,
        six: 6,
        seven: 7,
        eight: 8,
        nine: 9,
        ten: 10,
        eleven: 11,
        twelve: 12,
        thirteen: 13,
        fourteen: 14,
        fifteen: 15,
        sixteen: 16,
        seventeen: 17,
        eighteen: 18,
        nineteen: 19,
        twenty: 20,
        thirty: 30,
        forty: 40,
        fifty: 50,
        sixty: 60,
        seventy: 70,
        eighty: 80,
        ninety: 90,
        hundred: 100,
    };

    // First, try to find a direct number in the transcript
    const directNumber = transcript.match(/\d+/);
    if (directNumber) {
        return parseInt(directNumber[0]);
    }

    // Parse word numbers
    let result = 0;
    let words = transcript.split(/[\s-]+/);

    for (let word of words) {
        if (wordNumbers[word] !== undefined) {
            if (word === "hundred") {
                result = result === 0 ? 100 : result * 100;
            } else {
                result += wordNumbers[word];
            }
        }
    }

    return result > 0 ? result : null;
};

const focusQuarterInput = (quarter) => {
    setTimeout(() => {
        const input = document.querySelector(`#voice-term-${quarter}-input`);
        if (input) {
            input.focus();
        }
    }, 100);
};

// Global voice mode toggle (from header)
const toggleVoiceModeGlobal = () => {
    voiceModeEnabled.value = !voiceModeEnabled.value;

    if (voiceModeEnabled.value) {
        // Start table voice recognition when enabled from header
        startTableVoiceRecognition();
    } else {
        // Stop all voice recognition
        stopTableVoiceRecognition();
        if (speechRecognition.value) {
            try {
                speechRecognition.value.stop();
            } catch (e) {}
        }
        isVoiceActive.value = false;
        tableVoiceActive.value = false;
        voiceTranscript.value = "";
        voiceStatus.value = "Voice mode off";
        tableVoiceStatus.value = "Say a student name to select...";
        focusedGradeRow.value = null;
    }
};

// Toggle within modal
const toggleVoiceRecognition = () => {
    voiceModeEnabled.value = !voiceModeEnabled.value;

    if (voiceModeEnabled.value) {
        startVoiceRecognition();
    } else {
        stopVoiceRecognition();
    }
};

const startVoiceRecognition = () => {
    // Always reinitialize for fresh start
    speechRecognition.value = initVoiceRecognition();

    if (speechRecognition.value) {
        try {
            setTimeout(() => {
                try {
                    speechRecognition.value.start();
                    isVoiceActive.value = true;
                    currentVoiceQuarter.value = 1;
                    voiceStatus.value =
                        "🎤 Listening... Say a grade or term.";
                    focusQuarterInput(1);
                } catch (e) {
                    console.error("Failed to start recognition:", e);
                    voiceStatus.value = "Failed to start voice. Try again.";
                }
            }, 100);
        } catch (e) {
            console.error("Failed to start recognition:", e);
        }
    }
};

// Fully disable voice mode
const stopVoiceRecognition = () => {
    if (speechRecognition.value) {
        try {
            speechRecognition.value.stop();
        } catch (e) {
            console.log("Recognition stop failed");
        }
        speechRecognition.value = null;
    }
    isVoiceActive.value = false;
    voiceModeEnabled.value = false;
    voiceTranscript.value = "";
    voiceStatus.value = "Voice mode off";
};

// Pause voice (for modal close) without disabling mode
const pauseVoiceRecognition = () => {
    if (speechRecognition.value) {
        try {
            speechRecognition.value.stop();
        } catch (e) {}
    }
    isVoiceActive.value = false;
    voiceTranscript.value = "";
};

// Table Voice Recognition for student selection
const tableVoiceRecognition = ref(null);
let lastTableTranscript = "";

const initTableVoiceRecognition = () => {
    const SpeechRecognition =
        window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        toast.error("Voice recognition is not supported in your browser");
        return null;
    }

    const recognition = new SpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = "en-US";
    recognition.maxAlternatives = 3; // More alternatives for better accuracy

    recognition.onresult = (event) => {
        const result = event.results[event.results.length - 1];
        const fullTranscript = result[0].transcript.toLowerCase().trim();

        // Extract only the last few words (most recent speech)
        const words = fullTranscript.split(/\s+/);
        const recentWords = words.slice(-4).join(" "); // Only last 4 words for names
        const transcript = recentWords;

        voiceTranscript.value = `"${recentWords}"`;

        // Process table commands immediately
        if (transcript !== lastTableTranscript) {
            const processed = processTableVoiceCommand(
                transcript,
                result.isFinal,
            );
            if (processed) {
                lastTableTranscript = transcript;
                // Clear transcript quickly
                setTimeout(() => {
                    voiceTranscript.value = "";
                }, 800);
            }
        }

        // Reset for next command on final result
        if (result.isFinal) {
            lastTableTranscript = "";
        }
    };

    recognition.onerror = (event) => {
        console.error("Table voice recognition error:", event.error);
        if (event.error === "no-speech") {
            // Silent - keep listening
        } else if (event.error === "audio-capture") {
            tableVoiceStatus.value = "No microphone found.";
            stopTableVoiceRecognition();
        } else if (event.error === "not-allowed") {
            tableVoiceStatus.value = "Microphone access denied.";
            stopTableVoiceRecognition();
        } else if (event.error === "aborted") {
            // Silently restart
        }
    };

    recognition.onend = () => {
        if (
            tableVoiceActive.value &&
            voiceModeEnabled.value &&
            !showGradeModal.value
        ) {
            // Restart immediately without delay
            try {
                recognition.start();
            } catch (e) {
                // Retry after tiny delay
                setTimeout(() => {
                    try {
                        recognition.start();
                    } catch (e2) {}
                }, 50);
            }
        }
    };

    return recognition;
};

const processTableVoiceCommand = (transcript, isFinal) => {
    // Check for "edit" command - process immediately for snappy response
    if (
        transcript.includes("edit") ||
        transcript.includes("open") ||
        transcript.includes("enter")
    ) {
        if (focusedGradeRow.value) {
            tableVoiceStatus.value = `✓ Opening ${focusedGradeRow.value.student?.first_name}'s grades...`;
            // Stop table voice and open modal immediately
            stopTableVoiceRecognition();
            openGradeModal(focusedGradeRow.value);
            return true;
        } else {
            tableVoiceStatus.value = "⚠ No student selected. Say a name first.";
            return true;
        }
    }

    // Check for "cancel" or "clear" to deselect - immediate
    if (
        transcript.includes("cancel") ||
        transcript.includes("clear") ||
        transcript.includes("deselect")
    ) {
        focusedGradeRow.value = null;
        tableVoiceStatus.value = "Selection cleared. Say a student name...";
        return true;
    }

    // Try to find a matching student - immediate on interim
    const matchedGrade = findStudentByVoice(transcript);
    if (matchedGrade) {
        focusedGradeRow.value = matchedGrade;
        tableVoiceStatus.value = `✓ ${matchedGrade.student?.first_name} ${matchedGrade.student?.last_name} - Say "edit"`;
        // Scroll to the focused row
        scrollToFocusedRow(matchedGrade);
        return true;
    }

    return false;
};

const findStudentByVoice = (transcript) => {
    const grades = filteredGrades.value;
    let bestMatch = null;
    let bestScore = 0;

    // Normalize transcript
    const normalizedTranscript = transcript.toLowerCase().trim();
    const transcriptWords = normalizedTranscript.split(/\s+/);

    for (const grade of grades) {
        const firstName = (grade.student?.first_name || "")
            .toLowerCase()
            .trim();
        const lastName = (grade.student?.last_name || "").toLowerCase().trim();
        const fullName = `${firstName} ${lastName}`;
        const reverseName = `${lastName} ${firstName}`;
        const fullNameNoSpace = `${firstName}${lastName}`;
        const reverseNameNoSpace = `${lastName}${firstName}`;

        let score = 0;

        // ONLY match if BOTH first and last name are present

        // Full name match (first last)
        if (
            normalizedTranscript.includes(fullName) ||
            normalizedTranscript === fullName
        ) {
            score = 100;
        }
        // Reverse name match (last first)
        else if (
            normalizedTranscript.includes(reverseName) ||
            normalizedTranscript === reverseName
        ) {
            score = 100;
        }
        // Both names as separate words anywhere in transcript
        else if (
            transcriptWords.includes(firstName) &&
            transcriptWords.includes(lastName)
        ) {
            score = 95;
        }
        // Names said together without space (speech recognition sometimes merges)
        else if (
            normalizedTranscript.includes(fullNameNoSpace) ||
            normalizedTranscript.includes(reverseNameNoSpace)
        ) {
            score = 90;
        }
        // Fuzzy: first name exact + last name starts with OR last name exact + first name starts with
        else {
            let hasFirstName = transcriptWords.includes(firstName);
            let hasLastName = transcriptWords.includes(lastName);

            // Check for partial matches of the other name
            if (hasFirstName && !hasLastName) {
                for (const word of transcriptWords) {
                    if (word !== firstName && word.length >= 3) {
                        if (
                            lastName.startsWith(word) ||
                            word.startsWith(lastName)
                        ) {
                            score = 80;
                            break;
                        }
                    }
                }
            } else if (hasLastName && !hasFirstName) {
                for (const word of transcriptWords) {
                    if (word !== lastName && word.length >= 3) {
                        if (
                            firstName.startsWith(word) ||
                            word.startsWith(firstName)
                        ) {
                            score = 80;
                            break;
                        }
                    }
                }
            }
        }

        if (score > bestScore) {
            bestScore = score;
            bestMatch = grade;
        }
    }

    // Only return match if we have a strong full-name match
    if (bestScore >= 80) {
        return bestMatch;
    }

    // If no full name match, show helpful message
    if (transcript.length > 2) {
        tableVoiceStatus.value = `Say full name (e.g., "Balagtas Rafael")`;
    }

    return null;
};

const scrollToFocusedRow = (grade) => {
    const rowId = `grade-row-${grade.student?.id}-${grade.subject?.id}`;
    const row = document.getElementById(rowId);
    if (row) {
        row.scrollIntoView({ behavior: "smooth", block: "center" });
    }
};

const startTableVoiceRecognition = () => {
    // Always reinitialize for fresh start
    tableVoiceRecognition.value = initTableVoiceRecognition();

    if (tableVoiceRecognition.value) {
        try {
            setTimeout(() => {
                try {
                    tableVoiceRecognition.value.start();
                    tableVoiceActive.value = true;
                    tableVoiceStatus.value =
                        "🎤 Listening... Say a student name to select.";
                } catch (e) {
                    console.error("Failed to start table recognition:", e);
                    tableVoiceStatus.value =
                        "Failed to start voice. Try again.";
                }
            }, 100);
        } catch (e) {
            console.error("Failed to start table recognition:", e);
        }
    }
};

const stopTableVoiceRecognition = () => {
    if (tableVoiceRecognition.value) {
        try {
            tableVoiceRecognition.value.stop();
        } catch (e) {}
        tableVoiceRecognition.value = null;
    }
    tableVoiceActive.value = false;
};

const openStudentModal = (student) => {
    selectedStudent.value = student;
    showStudentModal.value = true;
};

const closeStudentModal = () => {
    showStudentModal.value = false;
    selectedStudent.value = null;
};

const openSubjectStudentsModal = (subject) => {
    selectedSubjectForView.value = subject;
    subjectStudentsSearch.value = "";
    showSubjectStudentsModal.value = true;
};

const closeSubjectStudentsModal = () => {
    showSubjectStudentsModal.value = false;
    selectedSubjectForView.value = null;
    subjectStudentsSearch.value = "";
};

const getSubjectStudents = (subjectId) => {
    // Get all students from studentGrades where subject matches
    const studentSet = new Set();
    const students = [];

    // Primary source: studentGrades - this contains all students in sections where teacher teaches
    if (props.studentGrades && props.studentGrades.length > 0) {
        props.studentGrades
            .filter((g) => g.subject_id === subjectId)
            .forEach((grade) => {
                if (grade.student && !studentSet.has(grade.student.id)) {
                    studentSet.add(grade.student.id);
                    students.push({
                        ...grade.student,
                        sectionName: grade.section?.name || "N/A",
                    });
                }
            });
    }

    return students;
};

const filteredSubjectStudents = computed(() => {
    if (!selectedSubjectForView.value) return [];

    let students = getSubjectStudents(selectedSubjectForView.value.id);

    if (subjectStudentsSearch.value) {
        const search = subjectStudentsSearch.value.toLowerCase();
        students = students.filter(
            (s) =>
                s.first_name?.toLowerCase().includes(search) ||
                s.last_name?.toLowerCase().includes(search) ||
                s.lrn?.toLowerCase().includes(search),
        );
    }

    return students;
});

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return d.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const submitGrades = () => {
    isSubmitting.value = true;

    // Get student_id and subject_id from selectedGrade
    const studentId = selectedGrade.value.student?.id;
    const subjectId = selectedGrade.value.subject_id;

    if (!studentId || !subjectId) {
        toast.error("Invalid student or subject");
        isSubmitting.value = false;
        return;
    }

    router.put(
        `/teacher/grades/${studentId}/${subjectId}`,
        {
            term_1: gradeForm.value.term_1,
            term_2: gradeForm.value.term_2,
            term_3: gradeForm.value.term_3,
            final_grade: computedFinalGrade.value,
            section_id: selectedGrade.value.section_id,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Grades updated successfully!");
                pauseVoiceRecognition();
                closeGradeModal();
            },
            onError: () => {
                toast.error("Failed to update grades.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

// Print Grades Function
const printGrades = () => {
    const gradesToPrint = filteredGrades.value;

    if (gradesToPrint.length === 0) {
        toast.warning("No grades to print");
        return;
    }

    // Get section name for the title
    let sectionName = "All Sections";
    if (selectedSectionFilter.value !== "all") {
        const section = props.teacherSections.find(
            (s) => s.id === selectedSectionFilter.value,
        );
        sectionName = section ? section.name : "Selected Section";
    }

    // Get subject name if filtered
    let subjectName = "";
    if (selectedSubjectFilter.value !== "all") {
        const subject = props.teacherSubjects.find(
            (s) => s.id === selectedSubjectFilter.value,
        );
        subjectName = subject ? ` - ${subject.name}` : "";
    }

    // Group grades by section for better organization
    const gradesBySection = {};
    gradesToPrint.forEach((grade) => {
        const sectionKey = grade.section?.name || "Unknown Section";
        if (!gradesBySection[sectionKey]) {
            gradesBySection[sectionKey] = [];
        }
        gradesBySection[sectionKey].push(grade);
    });

    // Build print HTML
    let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Student Grades - ${sectionName}${subjectName}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    font-size: 11px;
                    line-height: 1.4;
                    color: #333;
                    padding: 15px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #003366;
                }
                .header h1 {
                    font-size: 18px;
                    color: #003366;
                    margin-bottom: 5px;
                }
                .header h2 {
                    font-size: 14px;
                    color: #666;
                    font-weight: normal;
                }
                .header .school-year {
                    font-size: 12px;
                    color: #888;
                    margin-top: 5px;
                }
                .section-block {
                    margin-bottom: 25px;
                    page-break-inside: avoid;
                }
                .section-title {
                    background: #003366;
                    color: white;
                    padding: 8px 12px;
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 0;
                    text-align: center;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 6px 8px;
                    text-align: center;
                }
                th {
                    background: #f5f5f5;
                    font-weight: 600;
                    font-size: 10px;
                    text-transform: uppercase;
                    color: #555;
                }
                td {
                    font-size: 11px;
                }
                .student-name {
                    text-align: left;
                    font-weight: 500;
                }
                .lrn {
                    font-family: monospace;
                    font-size: 10px;
                    color: #666;
                }
                .grade-pass {
                    color: #059669;
                    font-weight: 600;
                }
                .grade-fail {
                    color: #dc2626;
                    font-weight: 600;
                }
                .final-grade {
                    font-weight: 700;
                    font-size: 12px;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #ddd;
                    display: flex;
                    justify-content: space-between;
                    font-size: 10px;
                    color: #666;
                }
                .signature-line {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-block {
                    text-align: center;
                    width: 200px;
                }
                .signature-block .line {
                    border-top: 1px solid #333;
                    margin-bottom: 5px;
                }
                .signature-block .label {
                    font-size: 10px;
                    color: #666;
                }
                @media print {
                    body { padding: 0; }
                    .section-block { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Tambo National High School</h1>
                <h2>Student Grades Report${subjectName}</h2>
                <div class="school-year">School Year 2025-2026</div>
            </div>
    `;

    // Generate tables for each section
    Object.keys(gradesBySection)
        .sort()
        .forEach((sectionKey) => {
            const sectionGrades = gradesBySection[sectionKey];

            // Sort by subject then by student name
            sectionGrades.sort((a, b) => {
                const subjectCompare = (a.subject?.name || "").localeCompare(
                    b.subject?.name || "",
                );
                if (subjectCompare !== 0) return subjectCompare;
                return (a.student?.last_name || "").localeCompare(
                    b.student?.last_name || "",
                );
            });

            printContent += `
            <div class="section-block">
                <div class="section-title">${sectionKey}</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Student Name</th>
                            <th style="width: 12%;">LRN</th>
                            <th style="width: 18%;">Subject</th>
                            <th style="width: 8%;">T1</th>
                            <th style="width: 8%;">T2</th>
                            <th style="width: 8%;">T3</th>
                            <th style="width: 10%;">Final</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

            sectionGrades.forEach((grade) => {
                const getGradeClass = (g) => {
                    if (!g) return "";
                    return g >= 75 ? "grade-pass" : "grade-fail";
                };

                printContent += `
                <tr>
                    <td class="student-name">${grade.student?.last_name || ""}, ${grade.student?.first_name || ""}</td>
                    <td class="lrn">${grade.student?.lrn || "N/A"}</td>
                    <td>${grade.subject?.name || "N/A"}</td>
                    <td class="${getGradeClass(grade.term_1)}">${grade.term_1 || "-"}</td>
                    <td class="${getGradeClass(grade.term_2)}">${grade.term_2 || "-"}</td>
                    <td class="${getGradeClass(grade.term_3)}">${grade.term_3 || "-"}</td>
                    <td class="final-grade ${getGradeClass(grade.final_grade)}">${grade.final_grade || "-"}</td>
                </tr>
            `;
            });

            printContent += `
                    </tbody>
                </table>
            </div>
        `;
        });

    // Add footer with signature lines
    printContent += `
            <div class="signature-line">
                <div class="signature-block">
                    <div class="line"></div>
                    <div class="label">Prepared by: ${props.user.first_name} ${props.user.last_name}</div>
                </div>
                <div class="signature-block">
                    <div class="line"></div>
                    <div class="label">Noted by: Principal</div>
                </div>
            </div>
            <div class="footer">
                <span>Printed on: ${new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" })}</span>
                <span>Total Students: ${gradesToPrint.length}</span>
            </div>
        </body>
        </html>
    `;

    // Open print window
    const printWindow = window.open("", "_blank");
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();

    // Trigger print after content loads
    setTimeout(() => {
        printWindow.print();
    }, 250);
};

// Toggle Select All Sections
const toggleSelectAllSections = () => {
    if (selectedPrintSections.value.length === props.teacherSections.length) {
        selectedPrintSections.value = [];
    } else {
        selectedPrintSections.value = props.teacherSections.map((s) => s.id);
    }
};

// Print Selected Sections
const printSelectedSections = () => {
    if (selectedPrintSections.value.length === 0) {
        toast.warning("Please select at least one section to print");
        return;
    }

    // Filter grades by selected sections
    const gradesToPrint = props.studentGrades.filter((g) =>
        selectedPrintSections.value.includes(g.section_id),
    );

    if (gradesToPrint.length === 0) {
        toast.warning("No grades found for selected sections");
        return;
    }

    // Get selected section names
    const selectedSectionNames = props.teacherSections
        .filter((s) => selectedPrintSections.value.includes(s.id))
        .map((s) => s.name);

    const sectionTitle =
        selectedSectionNames.length <= 3
            ? selectedSectionNames.join(", ")
            : `${selectedSectionNames.length} Sections`;

    // Get subject name if filtered
    let subjectName = "";
    if (selectedSubjectFilter.value !== "all") {
        const subject = props.teacherSubjects.find(
            (s) => s.id === selectedSubjectFilter.value,
        );
        subjectName = subject ? ` - ${subject.name}` : "";

        // Also filter by subject if selected
        const filteredBySubject = gradesToPrint.filter(
            (g) => g.subject_id === selectedSubjectFilter.value,
        );
        if (filteredBySubject.length > 0) {
            gradesToPrint.length = 0;
            gradesToPrint.push(...filteredBySubject);
        }
    }

    // Group grades by section
    const gradesBySection = {};
    gradesToPrint.forEach((grade) => {
        const sectionKey = grade.section?.name || "Unknown Section";
        if (!gradesBySection[sectionKey]) {
            gradesBySection[sectionKey] = [];
        }
        gradesBySection[sectionKey].push(grade);
    });

    // Build print HTML (same style as printGrades)
    let printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Student Grades - ${sectionTitle}${subjectName}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    font-size: 11px;
                    line-height: 1.4;
                    color: #333;
                    padding: 15px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 2px solid #003366;
                }
                .header h1 {
                    font-size: 18px;
                    color: #003366;
                    margin-bottom: 5px;
                }
                .header h2 {
                    font-size: 14px;
                    color: #666;
                    font-weight: normal;
                }
                .header .school-year {
                    font-size: 12px;
                    color: #888;
                    margin-top: 5px;
                }
                .section-block {
                    margin-bottom: 25px;
                    page-break-inside: avoid;
                }
                .section-title {
                    background: #003366;
                    color: white;
                    padding: 8px 12px;
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 0;
                    text-align: center;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 6px 8px;
                    text-align: center;
                }
                th {
                    background: #f5f5f5;
                    font-weight: 600;
                    font-size: 10px;
                    text-transform: uppercase;
                    color: #555;
                }
                td {
                    font-size: 11px;
                }
                .student-name {
                    text-align: left;
                    font-weight: 500;
                }
                .lrn {
                    font-family: monospace;
                    font-size: 10px;
                    color: #666;
                }
                .grade-pass {
                    color: #059669;
                    font-weight: 600;
                }
                .grade-fail {
                    color: #dc2626;
                    font-weight: 600;
                }
                .final-grade {
                    font-weight: 700;
                    font-size: 12px;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 15px;
                    border-top: 1px solid #ddd;
                    display: flex;
                    justify-content: space-between;
                    font-size: 10px;
                    color: #666;
                }
                .signature-line {
                    margin-top: 40px;
                    display: flex;
                    justify-content: space-between;
                }
                .signature-block {
                    text-align: center;
                    width: 200px;
                }
                .signature-block .line {
                    border-top: 1px solid #333;
                    margin-bottom: 5px;
                }
                .signature-block .label {
                    font-size: 10px;
                    color: #666;
                }
                @media print {
                    body { padding: 0; }
                    .section-block { page-break-inside: avoid; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Tambo National High School</h1>
                <h2>Student Grades Report${subjectName}</h2>
                <div class="school-year">School Year 2025-2026</div>
            </div>
    `;

    // Generate tables for each section
    Object.keys(gradesBySection)
        .sort()
        .forEach((sectionKey) => {
            const sectionGrades = gradesBySection[sectionKey];

            // Sort by subject then by student name
            sectionGrades.sort((a, b) => {
                const subjectCompare = (a.subject?.name || "").localeCompare(
                    b.subject?.name || "",
                );
                if (subjectCompare !== 0) return subjectCompare;
                return (a.student?.last_name || "").localeCompare(
                    b.student?.last_name || "",
                );
            });

            printContent += `
            <div class="section-block">
                <div class="section-title">${sectionKey}</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Student Name</th>
                            <th style="width: 12%;">LRN</th>
                            <th style="width: 18%;">Subject</th>
                            <th style="width: 8%;">T1</th>
                            <th style="width: 8%;">T2</th>
                            <th style="width: 8%;">T3</th>
                            <th style="width: 10%;">Final</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            sectionGrades.forEach((grade) => {
                const getGradeClass = (g) => {
                    if (!g) return "";
                    return g >= 75 ? "grade-pass" : "grade-fail";
                };

                printContent += `
                <tr>
                    <td class="student-name">${grade.student?.last_name || ""}, ${grade.student?.first_name || ""}</td>
                    <td class="lrn">${grade.student?.lrn || "N/A"}</td>
                    <td>${grade.subject?.name || "N/A"}</td>
                    <td class="${getGradeClass(grade.term_1)}">${grade.term_1 || "-"}</td>
                    <td class="${getGradeClass(grade.term_2)}">${grade.term_2 || "-"}</td>
                    <td class="${getGradeClass(grade.term_3)}">${grade.term_3 || "-"}</td>
                    <td class="final-grade ${getGradeClass(grade.final_grade)}">${grade.final_grade || "-"}</td>
                </tr>
            `;
            });

            printContent += `
                    </tbody>
                </table>
            </div>
        `;
        });

    // Add footer with signature lines
    printContent += `
            <div class="signature-line">
                <div class="signature-block">
                    <div class="line"></div>
                    <div class="label">Prepared by: ${props.user.first_name} ${props.user.last_name}</div>
                </div>
                <div class="signature-block">
                    <div class="line"></div>
                    <div class="label">Noted by: Principal</div>
                </div>
            </div>
            <div class="footer">
                <span>Printed on: ${new Date().toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" })}</span>
                <span>Total Records: ${gradesToPrint.length}</span>
            </div>
        </body>
        </html>
    `;

    // Open print window
    const printWindow = window.open("", "_blank");
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();

    // Trigger print after content loads
    setTimeout(() => {
        printWindow.print();
    }, 250);

    // Close dropdown and reset selections
    showPrintDropdown.value = false;
};

const logout = () => {
    router.post("/logout");
};

// Keyboard shortcut for voice mode (F2)
const handleKeyDown = (event) => {
    if (event.key === "F2") {
        event.preventDefault();
        if (activeNav.value === "grades") {
            toggleVoiceModeGlobal();
        }
    }
};

onMounted(() => {
    window.addEventListener("keydown", handleKeyDown);
    const nav = new URLSearchParams(window.location.search).get("nav");
    if (nav) {
        activeNav.value = nav;
    }
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeyDown);
});
</script>

<style scoped>
.dashboard-layout {
    display: flex;
    min-height: 100vh;
    background: #ececec;
}

/* Sidebar Styles */
.sidebar {
    width: 230px;
    background: #003366;
    color: white;
    display: flex;
    flex-direction: column;
    position: fixed;
    height: 100vh;
    z-index: 100;
    border-right: 1px solid #002244;
}

.sidebar-header {
    padding: 0.9rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border-bottom: 3px solid #c9a227;
}

.logo {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 2px solid white;
    object-fit: cover;
}

.school-info h2 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
}

.school-info p {
    margin: 0.15rem 0 0 0;
    font-size: 0.75rem;
    opacity: 0.85;
}

.sidebar-nav {
    flex: 1;
    padding: 0.35rem 0 0.75rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

.nav-group {
    padding: 0.15rem 0 0.25rem;
}

.nav-group + .nav-group {
    margin-top: 0.2rem;
    border-top: 1px solid #1a4a73;
    padding-top: 0.35rem;
}

.nav-group-label {
    display: block;
    padding: 0.4rem 1rem 0.2rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #c9a227;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.5rem 1rem;
    color: #e8eef4;
    text-decoration: none;
    border-left: 4px solid transparent;
}

.nav-item:hover {
    background: #00264d;
    color: white;
}

.nav-item.active {
    background: #002244;
    color: white;
    border-left-color: #c9a227;
}

.nav-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.nav-text {
    font-size: 0.88rem;
    font-weight: 500;
}

.sidebar-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #00264d;
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    width: 100%;
    padding: 0.55rem 0;
    background: transparent;
    border: none;
    color: #e8eef4;
    cursor: pointer;
    font-size: 0.88rem;
    font-weight: 500;
}

.logout-btn:hover {
    color: #fff;
    text-decoration: underline;
}

/* Main Content */
.main-wrapper {
    flex: 1;
    margin-left: 230px;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.dashboard-header {
    background: white;
    padding: 0.7rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #c9a227;
    position: sticky;
    top: 0;
    z-index: 50;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.header-content h1 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #003366;
}

.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    padding: 0.5rem;
    cursor: pointer;
    color: #003366;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.user-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.user-role {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #666;
}

.user-name {
    font-weight: 600;
    color: #003366;
    font-size: 0.88rem;
}

.user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 0;
    background: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.78rem;
    overflow: hidden;
}

.user-avatar .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mobile-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 90;
    opacity: 0;
    visibility: hidden;
}

.mobile-overlay.active {
    opacity: 1;
    visibility: visible;
}

.dashboard-main {
    flex: 1;
    padding: 1.25rem 1.5rem 2rem;
}

.gov-dashboard {
    width: 100%;
}

.gov-pagehead {
    margin-bottom: 1.35rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #cfcfcf;
}

.gov-kicker {
    margin: 0 0 0.25rem;
    font-size: 0.78rem;
    color: #555;
}

.gov-pagehead-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
}

.gov-pagehead h2 {
    margin: 0;
    color: #003366;
    font-size: 1.2rem;
}

.gov-sy {
    font-size: 0.85rem;
    color: #333;
    font-weight: 600;
}

.gov-section {
    margin-bottom: 1.75rem;
}

.gov-section-title {
    margin: 0 0 0.65rem;
    color: #003366;
    font-size: 0.95rem;
    font-weight: 700;
    padding-bottom: 0.3rem;
    border-bottom: 2px solid #c9a227;
}

.gov-stat-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.85rem;
}

.gov-stat-box {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.gov-stat-label {
    background: #003366;
    color: #fff;
    padding: 0.4rem 0.7rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.gov-stat-value {
    padding: 0.85rem 0.7rem 0.95rem;
    font-size: 1.7rem;
    font-weight: 700;
    color: #003366;
    text-align: center;
}

.gov-link-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.85rem;
}

.gov-link-box {
    background: #fff;
    border: 1px solid #c5c5c5;
    color: #003366;
    padding: 1rem 0.85rem;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
}

.gov-link-box:hover {
    background: #003366;
    color: #fff;
    border-color: #003366;
}

/* Welcome Banner */
.welcome-banner {
    background: #003366;
    color: white;
    padding: 1rem 1.15rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #c9a227;
}

.banner-content {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.banner-icon {
    display: none;
}

.banner-text h2 {
    margin: 0 0 0.2rem 0;
    font-size: 1.1rem;
    font-weight: 700;
}

.banner-text p {
    margin: 0;
    font-size: 0.85rem;
}

.school-year-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid rgba(255, 255, 255, 0.45);
    padding: 0.3rem 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.stat-card {
    background: white;
    padding: 0.85rem 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid #c5c5c5;
}

.stat-card:hover {
    background: #f7f7f7;
}

.stat-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2f6;
    color: #003366;
}

.stat-icon.advisory,
.stat-icon.subjects,
.stat-icon.sections,
.stat-icon.pending {
    background: #eef2f6;
    color: #003366;
}

.stat-info h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #003366;
}

.stat-info p {
    margin: 0.15rem 0 0 0;
    font-size: 0.78rem;
    color: #555;
    font-weight: 600;
}

/* Quick Actions */
.quick-actions {
    background: white;
    padding: 0;
    border: 1px solid #c5c5c5;
}

.quick-actions h3 {
    margin: 0;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    background: #003366;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0;
}

.action-card {
    display: block;
    text-align: left;
    padding: 0.7rem 0.85rem;
    background: #fff;
    border: none;
    border-right: 1px solid #e4e4e4;
    cursor: pointer;
}

.action-card:last-child {
    border-right: none;
}

.action-card:hover {
    background: #f4f7fb;
}

.action-icon {
    display: none;
}

.action-card span {
    font-weight: 600;
    color: #003366;
    font-size: 0.9rem;
    text-decoration: underline;
}

/* Section Header */
.section-header {
    margin-bottom: 1.5rem;
}

.section-info h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
}

.section-info p {
    margin: 0.5rem 0 0 0;
    color: #64748b;
}

.no-advisory {
    color: #ef4444;
    font-style: italic;
}

/* Advisory Header Card */
.advisory-header-card {
    background: #003366;
    color: #fff;
    padding: 0.9rem 1.1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    border-bottom: 3px solid #c9a227;
}

.advisory-header-card::before {
    display: none;
}

.advisory-header-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    z-index: 1;
}

.advisory-icon {
    width: 40px;
    height: 40px;
    background: #00264d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.advisory-details h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
}

.year-level-text {
    display: block;
    font-size: 0.85rem;
    color: #e8eef4;
    margin-top: 0.2rem;
}

.advisory-header-right {
    z-index: 1;
}

.school-year-display {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.65rem;
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: white;
}

.sy-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: white;
}

/* Advisory Stats Grid */
.advisory-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.advisory-stat-card {
    background: white;
    padding: 0.75rem 0.85rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid #c5c5c5;
}

.advisory-stat-card:hover {
    background: #f7f7f7;
}

.stat-card-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #eef2f6;
    color: #003366;
}

.advisory-stat-card.total .stat-card-icon,
.advisory-stat-card.male .stat-card-icon,
.advisory-stat-card.female .stat-card-icon {
    background: #eef2f6;
    color: #003366;
}

.stat-card-content {
    display: flex;
    flex-direction: column;
}

.stat-card-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-card-label {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 0.15rem;
}

/* Table Header Bar */
.table-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
    gap: 1rem;
}

.table-header-bar h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.table-controls .search-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    min-width: 200px;
}

.table-controls .search-box:focus-within {
    border-color: #003366;
    box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
}

.table-controls .search-box svg {
    color: #94a3b8;
    flex-shrink: 0;
}

.table-controls .search-input {
    border: none;
    outline: none;
    font-size: 0.875rem;
    width: 100%;
    background: transparent;
}

.table-controls .filter-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    color: #334155;
    cursor: pointer;
    min-width: 120px;
}

.table-controls .filter-select:focus {
    outline: none;
    border-color: #003366;
    box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
}

.table-count {
    font-size: 0.85rem;
    color: #64748b;
    padding: 0.25rem 0.75rem;
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
}

/* Icon Action Button */
.icon-action-btn {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.icon-action-btn.view {
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4338ca;
}

.icon-action-btn.view:hover {
    background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(67, 56, 202, 0.25);
}

.advisory-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}

.sf-mini-link {
    border: 1px solid #003366;
    background: #fff;
    color: #003366;
    padding: 0.2rem 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    text-decoration: none;
}

/* Stats Mini Grid - Keep for backward compatibility */
.stats-mini-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-mini-card {
    background: white;
    border-radius: 12px;
    padding: 1rem 0.75rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
}

.stat-mini-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-mini-value.male {
    color: #2563eb;
}

.stat-mini-value.female {
    color: #ec4899;
}

.stat-mini-label {
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.25rem;
}

/* Table Voice Bar */
.table-voice-bar {
    background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
    color: white;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.table-voice-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.table-voice-status .voice-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.table-voice-status .pulse-dot {
    width: 8px;
    height: 8px;
    background: #22c55e;
    border-radius: 50%;
    animation: pulse 1.5s infinite;
}

.table-voice-status .mic-icon-active {
    color: #22c55e;
}

.table-voice-text {
    font-weight: 500;
    font-size: 0.9rem;
}

.table-voice-transcript {
    font-style: italic;
    opacity: 0.85;
    font-size: 0.85rem;
    padding: 0.25rem 0.5rem;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 4px;
}

.table-voice-commands {
    display: flex;
    gap: 0.5rem;
}

.command-tag {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Voice Active Table */
.voice-active-table tbody tr {
    cursor: pointer;
    transition: all 0.2s ease;
}

.voice-active-table tbody tr:hover {
    background: #f0f7ff;
}

.voice-focused-row {
    background: #e8eef4 !important;
    outline: 2px solid #003366;
}

.voice-focused-row td {
    font-weight: 600;
}

@keyframes focusPulse {
    0%,
    100% {
        box-shadow: inset 0 0 0 2px #003366;
    }
    50% {
        box-shadow: inset 0 0 0 3px #0055a4;
    }
}

/* Data Table */
.data-table-container {
    background: white;
    overflow: hidden;
    border: 1px solid #c5c5c5;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 220px);
    margin-top: 0.75rem;
}

.grades-table-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    flex-shrink: 0;
}

.data-table-container .table-header-bar {
    flex-shrink: 0;
}

.data-table-wrapper {
    overflow-y: auto;
    flex: 1;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #e8eef4;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table th {
    padding: 0.55rem 0.7rem;
    text-align: left;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #003366;
    border-bottom: 1px solid #c5c5c5;
    background: #e8eef4;
}

.data-table td {
    padding: 0.5rem 0.7rem;
    border-bottom: 1px solid #ddd;
    color: #222;
    font-size: 0.88rem;
}

.data-table tbody tr:hover {
    background: #f4f7fb;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-sm {
    width: 28px;
    height: 28px;
    border-radius: 0;
    background: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.7rem;
    flex-shrink: 0;
}

.user-info-cell {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.user-name-cell {
    font-weight: 600;
    color: #003366;
}

.user-meta {
    font-size: 0.8rem;
    color: #555;
}

.lrn-badge {
    background: #fff;
    color: #333;
    padding: 0.1rem 0.35rem;
    border: 1px solid #ccc;
    font-size: 0.8rem;
    font-family: monospace;
}

.gender-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.gender-badge.male {
    background: #dbeafe;
    color: #1d4ed8;
}

.gender-badge.female {
    background: #fce7f3;
    color: #be185d;
}

.guardian-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.guardian-info small {
    color: #64748b;
    font-size: 0.8rem;
}

/* Advisory Table Refinements */
.advisory-table .text-center {
    text-align: center;
}

.index-cell {
    width: 50px;
}

.student-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.8rem;
    color: #475569;
}

.student-row:hover .student-number {
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    color: white;
}

.user-avatar-sm {
    width: 28px;
    height: 28px;
    border-radius: 0;
    background: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.75rem;
    flex-shrink: 0;
    overflow: hidden;
}

.user-avatar-sm .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: #475569;
}

.contact-item svg {
    color: #94a3b8;
    flex-shrink: 0;
}

.text-muted {
    color: #94a3b8;
    font-style: italic;
    font-size: 0.85rem;
}

.guardian-info-compact {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.guardian-name {
    font-weight: 500;
    color: #334155;
    font-size: 0.875rem;
}

.guardian-contact {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
    color: #64748b;
}

.action-btn.view {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1d4ed8;
    padding: 0.5rem 0.85rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.action-btn.view:hover {
    background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

.action-btn.view span {
    font-weight: 500;
}

/* Grade Cells */
.grade-cell {
    display: inline-block;
    font-weight: 600;
    font-size: 0.85rem;
}

.grade-cell.excellent {
    color: #1f6b3a;
}

.grade-cell.very-good {
    color: #003366;
}

.grade-cell.good {
    color: #003366;
}

.grade-cell.satisfactory {
    color: #9a6700;
}

.grade-cell.needs-improvement {
    color: #9b1c1c;
}

.grade-cell.final {
    font-weight: 700;
    text-decoration: underline;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: auto;
    height: auto;
    border-radius: 0;
    border: 1px solid #003366;
    background: #fff;
    color: #003366;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.2rem 0.4rem;
}

.action-btn.edit {
    background: #fff;
    color: #003366;
}

.action-btn.edit:hover {
    background: #003366;
    color: #fff;
}

.action-btn.view {
    background: #fff;
    color: #003366;
}

.action-btn.view:hover {
    background: #003366;
    color: #fff;
}

/* Address Cell */
.address-cell {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 1.5rem;
    color: #555;
}

.empty-state-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.empty-state-content p {
    margin: 0;
    font-size: 0.9rem;
}

.empty-advisory {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.empty-advisory-content {
    padding: 0.9rem 1rem 1rem;
    color: #222;
}

.empty-advisory-content p {
    margin: 0 0 0.4rem;
    max-width: none;
    line-height: 1.45;
    color: #333;
    font-size: 0.9rem;
}

.empty-advisory-content p:last-child {
    margin-bottom: 0;
}

/* Subjects Handled Section */
.subjects-header-card {
    background: #003366;
    color: #fff;
    padding: 0.9rem 1.1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    border-bottom: 3px solid #c9a227;
}

.subjects-header-card::before {
    display: none;
}

.subjects-header-left {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    z-index: 1;
}

.subjects-icon {
    width: 40px;
    height: 40px;
    background: #00264d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.subjects-details h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
}

.subjects-count-text {
    display: block;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
    margin-top: 0.35rem;
}

.subjects-header-right {
    z-index: 1;
}

.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.25rem;
}

.subject-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.subject-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.subject-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, #f0f7ff 0%, #dbeafe 100%);
    border-bottom: 1px solid #e2e8f0;
}

.subject-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.subject-info {
    flex: 1;
    min-width: 0;
}

.subject-name {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
}

.subject-code {
    display: inline-block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: #003366;
    font-weight: 500;
    background: rgba(0, 51, 102, 0.1);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

.subject-card-body {
    padding: 1.25rem;
}

.subject-detail {
    margin-bottom: 1rem;
}

.subject-detail:last-child {
    margin-bottom: 0;
}

.detail-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.detail-value {
    margin: 0;
    font-size: 0.9rem;
    color: #475569;
    line-height: 1.5;
}

.subject-sections {
    margin-top: 1rem;
}

.section-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.section-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4338ca;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.empty-subjects {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.empty-notice-bar {
    background: #003366;
    color: #fff;
    padding: 0.45rem 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-bottom: 3px solid #c9a227;
}

.empty-subjects-content {
    padding: 0.9rem 1rem 1rem;
    color: #222;
}

.empty-subjects-content p {
    margin: 0 0 0.4rem;
    max-width: none;
    line-height: 1.45;
    color: #333;
    font-size: 0.9rem;
}

.empty-subjects-content p:last-child {
    margin-bottom: 0;
}

/* Subject Card Actions */
.subject-card-actions {
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
}

.view-students-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
    justify-content: center;
}

.view-students-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
}

/* Subject Students Modal */
.subject-students-modal .modal-header.gradient-header {
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    padding: 1.5rem;
}

.subject-students-modal .header-text {
    display: flex;
    flex-direction: column;
}

.subject-students-modal .header-subtitle {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 0.25rem;
}

.subject-students-body {
    padding: 1.5rem;
    max-height: calc(90vh - 180px);
    overflow-y: auto;
}

.modal-search-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box-modal {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.6rem 0.875rem;
    flex: 1;
    min-width: 200px;
}

.search-box-modal:focus-within {
    border-color: #003366;
    box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
}

.search-box-modal svg {
    color: #94a3b8;
    flex-shrink: 0;
}

.search-box-modal .search-input {
    border: none;
    outline: none;
    font-size: 0.875rem;
    width: 100%;
    background: transparent;
}

.students-count-badge {
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4338ca;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
}

.subject-students-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.subject-student-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.subject-student-card:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.student-card-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.student-card-avatar .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-card-avatar .avatar-initials {
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

.student-card-info {
    flex: 1;
    min-width: 0;
}

.student-card-name {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
}

.student-card-meta {
    display: flex;
    gap: 1rem;
    margin-top: 0.35rem;
    flex-wrap: wrap;
}

.student-card-meta .meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    color: #64748b;
}

.student-card-meta .meta-item svg {
    color: #94a3b8;
}

.student-card-actions {
    flex-shrink: 0;
}

.empty-subject-students {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: #94a3b8;
    text-align: center;
}

.empty-subject-students p {
    margin: 1rem 0 0 0;
    color: #64748b;
}

/* Management Header */
.management-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.85rem;
    flex-wrap: wrap;
}

.search-filter-container {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
}

.search-box {
    position: relative;
    min-width: 220px;
}

.search-icon {
    position: absolute;
    left: 0.55rem;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.search-input {
    width: 100%;
    padding: 0.45rem 0.55rem 0.45rem 2.1rem;
    border: 1px solid #bdbdbd;
    border-radius: 0;
    font-size: 0.88rem;
}

.search-input:focus {
    outline: none;
    border-color: #003366;
}

.filter-select {
    padding: 0.45rem 0.55rem;
    border: 1px solid #bdbdbd;
    border-radius: 0;
    font-size: 0.88rem;
    background: white;
    min-width: 140px;
}

.filter-select:focus {
    outline: none;
    border-color: #003366;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}

/* Higher z-index for student modal to appear on top of subject students modal */
.modal-overlay.student-modal-overlay {
    z-index: 1100;
}

.modal-container {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
}

.modal-container.large {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    color: white;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    padding: 0.375rem;
    border-radius: 6px;
    cursor: pointer;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

/* Professional Grade Modal Styles */
.grade-modal {
    max-width: 900px;
    width: 95%;
    border-radius: 20px;
    overflow: hidden;
}

.grade-modal-header {
    background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
    padding: 1.25rem 1.5rem;
    border-bottom: none;
}

.grade-header-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.grade-header-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.grade-header-text h3 {
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
}

.grade-header-text p {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.85rem;
    margin: 0.25rem 0 0 0;
}

.grade-modal-body {
    padding: 1.5rem;
    background: #f8fafc;
}

/* Landscape Layout */
.grade-modal-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 1.5rem;
    align-items: start;
}

.grade-left-column {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.grade-right-column {
    display: flex;
    flex-direction: column;
}

/* Student Info Card */
.student-info-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: white;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.student-avatar-large {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.student-avatar-large .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-avatar-large .avatar-initials {
    color: white;
    font-size: 1.25rem;
    font-weight: 700;
}

.student-details-section {
    flex: 1;
    min-width: 0;
}

.student-full-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
}

.student-meta-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.subject-badge {
    background: #dbeafe;
    color: #1e40af;
}

.section-badge {
    background: #f3e8ff;
    color: #7c3aed;
}

.lrn-meta-badge {
    background: #f1f5f9;
    color: #475569;
}

/* Grades Section */
.grades-section {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    height: 100%;
}

.section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.title-text {
    font-weight: 700;
    color: #1e293b;
    font-size: 1rem;
}

.title-hint {
    font-size: 0.75rem;
    color: #94a3b8;
}

.quarter-grades-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

/* Vertical layout for horizontal cards */
.quarter-grades-grid-vertical {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.quarter-card-horizontal {
    display: grid;
    grid-template-columns: 100px 1fr 120px;
    align-items: center;
    gap: 1rem;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.quarter-card-horizontal:hover {
    border-color: #003366;
    background: #f0f7ff;
}

.quarter-card-horizontal.active {
    border-color: #003366;
    border-width: 3px;
    background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
    box-shadow:
        0 0 0 4px rgba(0, 51, 102, 0.25),
        0 4px 12px rgba(0, 51, 102, 0.2);
    transform: scale(1.02);
    transition: all 0.15s ease;
}

.quarter-card-horizontal.active .quarter-label {
    color: #003366;
    font-weight: 700;
}

.quarter-card-horizontal.has-grade {
    border-color: #22c55e;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.quarter-card-horizontal.active.has-grade {
    border-color: #003366;
    background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
    box-shadow:
        0 0 0 4px rgba(0, 51, 102, 0.25),
        0 4px 12px rgba(0, 51, 102, 0.2);
}

.quarter-info {
    display: flex;
    flex-direction: column;
}

.quarter-info .quarter-label {
    font-size: 1rem;
    font-weight: 700;
    color: #003366;
}

.quarter-info .quarter-title {
    font-size: 0.75rem;
    color: #64748b;
}

.quarter-input-wrapper {
    flex: 1;
}

.quarter-input-wrapper .quarter-input {
    width: 100%;
    max-width: 120px;
}

.quarter-card-horizontal .grade-indicator {
    margin: 0;
    text-align: center;
    min-width: 100px;
}

.grade-indicator.empty {
    background: #f1f5f9;
    color: #94a3b8;
}

/* Saving Indicator */
.saving-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
    color: white;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    animation: fadeIn 0.3s ease;
}

.saving-indicator .spin {
    animation: spin 1s linear infinite;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.quarter-card {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.75rem;
    text-align: center;
    transition: all 0.3s ease;
}

.quarter-card:hover {
    border-color: #003366;
    background: #f0f7ff;
}

.quarter-card.active {
    border-color: #003366;
    background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
    box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.15);
}

.quarter-card.has-grade {
    border-color: #22c55e;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
}

.quarter-header {
    margin-bottom: 0.5rem;
}

.quarter-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 700;
    color: #003366;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.quarter-title {
    display: block;
    font-size: 0.65rem;
    color: #64748b;
}

.quarter-input {
    width: 100%;
    padding: 0.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1.25rem;
    font-weight: 700;
    text-align: center;
    color: #1e293b;
    background: white;
    transition: all 0.3s ease;
}

.quarter-input:focus {
    outline: none;
    border-color: #003366;
    box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
}

.quarter-input::placeholder {
    color: #cbd5e1;
}

.grade-indicator {
    margin-top: 0.4rem;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.grade-indicator.excellent {
    background: #dcfce7;
    color: #166534;
}

.grade-indicator.very-good {
    background: #dbeafe;
    color: #1e40af;
}

.grade-indicator.good {
    background: #fef3c7;
    color: #92400e;
}

.grade-indicator.satisfactory {
    background: #ffedd5;
    color: #c2410c;
}

.grade-indicator.needs-improvement {
    background: #fee2e2;
    color: #dc2626;
}

/* Final Grade Section */
.final-grade-section {
    margin-top: 0.25rem;
}

.final-grade-card {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.final-grade-card.excellent {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-color: #22c55e;
}

.final-grade-card.very-good {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #3b82f6;
}

.final-grade-card.good {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
}

.final-grade-card.satisfactory {
    background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
    border-color: #f97316;
}

.final-grade-card.needs-improvement {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-color: #ef4444;
}

.final-grade-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.final-grade-value {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
}

.final-grade-status {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-top: 0.25rem;
}

/* Grade Modal Footer */
.grade-modal-footer {
    background: white;
    padding: 0.75rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.grade-modal-footer .btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.btn-save-grades {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
}

.btn-save-grades:hover:not(:disabled) {
    background: linear-gradient(135deg, #002244 0%, #004488 100%);
}

.student-grade-info {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
}

.student-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
}

.subject-name {
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 0.25rem;
}

/* Voice Control Styles */
.voice-control-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0;
    padding: 0.5rem 0.75rem;
    background: linear-gradient(135deg, #dcfce7 0%, #d1fae5 100%);
    border: 2px solid #22c55e;
    border-radius: 8px;
}

.voice-control-section.voice-off {
    background: #f1f5f9;
    border: 2px solid transparent;
}

.voice-mode-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.mic-icon-active {
    color: #16a34a;
    animation: pulse-mic 1.5s infinite;
}

@keyframes pulse-mic {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.voice-mode-label {
    font-weight: 600;
    color: #16a34a;
    font-size: 0.9rem;
}

.voice-disable-btn {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.3rem 0.6rem;
    background: white;
    border: 1px solid #dc2626;
    color: #dc2626;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    margin-left: 0.5rem;
    transition: all 0.2s ease;
}

.voice-disable-btn:hover {
    background: #dc2626;
    color: white;
}

/* Unfinished Grades Filter Button */
.unfinished-filter-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.7rem;
    border: 1px solid #9a6700;
    background: white;
    color: #9a6700;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
}

.unfinished-filter-btn:hover {
    background: #9a6700;
    color: white;
}

.unfinished-filter-btn.active {
    background: #9a6700;
    border-color: #9a6700;
    color: white;
}

.unfinished-filter-btn:active {
    background: #7a5200;
}

.print-grades-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.7rem;
    border: 1px solid #003366;
    background: white;
    color: #003366;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
}

.print-grades-btn:hover {
    background: #003366;
    color: white;
}

.print-grades-btn:active {
    background: #00264d;
}

.print-grades-btn svg.rotate-180 {
    transform: rotate(180deg);
}

/* Print Dropdown Container */
.print-dropdown-container {
    position: relative;
}

.print-dropdown-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99;
}

.print-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    width: 320px;
    background: white;
    border: 1px solid #c5c5c5;
    z-index: 100;
    overflow: hidden;
}

.print-dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    background: #003366;
    color: #fff;
    border-bottom: 3px solid #c9a227;
}

.print-dropdown-header span {
    font-weight: 600;
    color: #fff;
    font-size: 0.85rem;
}

.select-all-btn {
    padding: 3px 8px;
    font-size: 0.72rem;
    color: #003366;
    background: white;
    border: 1px solid #fff;
    cursor: pointer;
    font-weight: 600;
}

.select-all-btn:hover {
    background: #e8eef4;
    color: #003366;
}

.print-section-list {
    max-height: 250px;
    overflow-y: auto;
    padding: 8px 0;
}

.print-section-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.print-section-item:hover {
    background: #f1f5f9;
}

.section-checkbox {
    display: none;
}

.checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.print-section-item:has(.section-checkbox:checked) .checkmark {
    background: #003366;
    border-color: #003366;
    color: white;
}

.section-label {
    flex: 1;
    font-size: 0.9rem;
    color: #334155;
    font-weight: 500;
}

.section-year {
    font-size: 0.75rem;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 4px;
}

.print-dropdown-footer {
    display: flex;
    gap: 10px;
    padding: 12px 16px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.print-cancel-btn {
    flex: 1;
    padding: 7px 12px;
    border: 1px solid #bdbdbd;
    background: white;
    color: #333;
    font-weight: 600;
    cursor: pointer;
}

.print-cancel-btn:hover {
    background: #f4f4f4;
}

.print-confirm-btn {
    flex: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 12px;
    border: none;
    background: #003366;
    color: white;
    font-weight: 600;
    cursor: pointer;
}

.print-confirm-btn:hover:not(:disabled) {
    background: #00264d;
}

.print-confirm-btn:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
}

/* Voice Mode Master Toggle in Header */
.voice-mode-toggle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.7rem;
    border: 1px solid #555;
    background: white;
    color: #333;
    font-weight: 600;
    font-size: 0.82rem;
    cursor: pointer;
    margin-left: auto;
}

.voice-mode-toggle:hover {
    border-color: #003366;
    color: #003366;
}

.voice-mode-toggle.active {
    background: #1f6b3a;
    border-color: #1f6b3a;
    color: white;
}

.voice-mode-toggle.active .shortcut-key {
    background: #fff;
    border-color: #fff;
    color: #1f6b3a;
}

.shortcut-key {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 1px 5px;
    font-size: 0.68rem;
    font-weight: 700;
    background: #eee;
    border: 1px solid #bbb;
    color: #333;
    margin-left: 4px;
}

.voice-toggle-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border: 2px solid #003366;
    background: white;
    color: #003366;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.voice-toggle-btn:hover {
    background: #003366;
    color: white;
}

.voice-toggle-btn.active {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
    animation: pulse-bg 2s infinite;
}

@keyframes pulse-bg {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
    }
    50% {
        box-shadow: 0 0 0 8px rgba(220, 38, 38, 0);
    }
}

.voice-status-container {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
}

.voice-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pulse-dot {
    width: 10px;
    height: 10px;
    background: #dc2626;
    border-radius: 50%;
    animation: pulse-dot 1.5s infinite;
}

@keyframes pulse-dot {
    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.3);
        opacity: 0.7;
    }
}

.voice-label {
    font-size: 0.85rem;
    color: #dc2626;
    font-weight: 500;
}

.current-quarter-badge {
    background: #003366;
    color: white;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.9rem;
}

.voice-status-bar {
    background: linear-gradient(135deg, #003366 0%, #0055a4 100%);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    margin-bottom: 0;
}

.voice-status-text {
    font-size: 0.9rem;
    font-weight: 500;
}

.voice-transcript {
    font-style: italic;
    opacity: 0.9;
    margin-top: 0.25rem;
    font-size: 0.85rem;
}

.voice-commands {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.command-hint {
    font-size: 0.75rem;
    opacity: 0.8;
}

.form-group.voice-active-input {
    position: relative;
}

.form-group.voice-active-input::before {
    content: "";
    position: absolute;
    inset: -4px;
    border: 3px solid #003366;
    border-radius: 12px;
    animation: voice-focus 1.5s infinite;
}

.form-group.voice-active-input label {
    color: #003366;
    font-weight: 700;
}

.form-group.voice-active-input input {
    border-color: #003366;
    background: #f0f7ff;
}

@keyframes voice-focus {
    0%,
    100% {
        border-color: #003366;
        box-shadow: 0 0 0 0 rgba(0, 51, 102, 0.3);
    }
    50% {
        border-color: #0055a4;
        box-shadow: 0 0 10px rgba(0, 51, 102, 0.2);
    }
}

.modal-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}

.form-group input {
    padding: 0.625rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    text-align: center;
}

.form-group input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.computed-final {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-top: 0.5rem;
}

.final-label {
    font-weight: 500;
    color: #475569;
}

.final-value {
    font-size: 1.25rem;
    font-weight: 700;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
}

.btn-primary {
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: white;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #f8fafc;
}

/* Enhanced Student Modal */
.student-modal .modal-header.gradient-header {
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    padding: 1.5rem;
}

.gradient-header .header-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.gradient-header .header-icon {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem;
    border-radius: 10px;
}

.gradient-header h3 {
    display: inline;
}

.student-modal-body {
    padding: 1.75rem;
    max-height: calc(90vh - 180px);
    overflow-y: auto;
}

.student-profile-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.75rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    margin-bottom: 1.75rem;
    border: 1px solid #e2e8f0;
}

.profile-avatar-large {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.75rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 4px solid white;
    box-shadow: 0 4px 12px rgba(0, 51, 102, 0.15);
}

.profile-avatar-large .avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-large .avatar-initials {
    font-size: 1.75rem;
}

.profile-main-info {
    flex: 1;
}

.student-full-name {
    margin: 0 0 0.75rem 0;
    font-size: 1.35rem;
    font-weight: 700;
    color: #1e293b;
}

.student-badges {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.lrn-badge.large {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.875rem;
    padding: 0.45rem 0.9rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 8px;
    font-weight: 600;
}

.gender-badge.large {
    padding: 0.45rem 0.9rem;
    font-size: 0.875rem;
    border-radius: 8px;
    font-weight: 600;
}

.info-sections-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.info-section {
    background: white;
    border-radius: 14px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.875rem;
    border-bottom: 2px solid #f1f5f9;
}

.section-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.section-icon.personal {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.section-icon.address {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.section-icon.guardian {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.section-header h5 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: #1e293b;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.info-grid.single-column {
    grid-template-columns: 1fr;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item label svg {
    color: #94a3b8;
}

.info-item span {
    font-size: 0.95rem;
    color: #1e293b;
    font-weight: 500;
}

.info-item .gwa-value {
    display: inline-block;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
}

.info-item .address-text {
    line-height: 1.5;
    color: #475569;
}

/* Old styles kept for compatibility */
.student-profile-header {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.profile-avatar-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003366 0%, #0066cc 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.5rem;
    flex-shrink: 0;
    overflow: hidden;
}

.profile-avatar-lg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.profile-info h4 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
}

.lrn-badge.large {
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
}

.detail-sections {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.detail-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e2e8f0;
}

.detail-section h5 {
    margin: 0 0 1rem 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e2e8f0;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.detail-item label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-item span {
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
}

/* Spinner */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Modal Transitions */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
    transform: scale(0.95);
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .gov-stat-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .gov-link-row {
        grid-template-columns: 1fr;
    }

    .advisory-stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.mobile-open {
        transform: translateX(0);
    }

    .main-wrapper {
        margin-left: 0;
    }

    .mobile-menu-btn {
        display: block;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .gov-stat-row {
        grid-template-columns: 1fr;
    }

    .stats-mini-grid {
        grid-template-columns: 1fr;
    }

    .advisory-stats-grid {
        grid-template-columns: 1fr;
    }

    .advisory-header-card {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .advisory-header-left {
        flex-direction: column;
    }

    .subjects-header-card {
        flex-direction: column;
        text-align: center;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .subjects-header-left {
        flex-direction: column;
    }

    .subjects-grid {
        grid-template-columns: 1fr;
    }

    .advisory-meta {
        justify-content: center;
    }

    .school-year-display {
        padding: 0.75rem 1.25rem;
    }

    .sy-value {
        font-size: 1.25rem;
    }

    .form-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }

    .welcome-banner {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .gov-pagehead-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .banner-content {
        flex-direction: column;
    }

    .student-profile-card {
        flex-direction: column;
        text-align: center;
    }

    .student-badges {
        justify-content: center;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }
}
</style>
