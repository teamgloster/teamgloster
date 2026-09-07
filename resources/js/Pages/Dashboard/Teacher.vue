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
                <div class="header-right">
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
                    <button
                        type="button"
                        class="header-logout-btn"
                        @click="logout"
                    >
                        <LogOut :size="16" />
                        <span class="logout-text">Logout</span>
                    </button>
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
                                <BookOpen :size="16" />
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
                                    <BookOpen :size="16" />
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
                                    <Link
                                        class="view-students-btn"
                                        :href="`/teacher/subjects/${subject.id}/students`"
                                    >
                                        <Users :size="16" />
                                        <span>View Students</span>
                                    </Link>
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
                    <p class="grade-once-note">
                        Enter grades once. The same ratings are used on both
                        the SF9 report card and the SP-10 / SF10 permanent
                        record. Select a term first — you can only encode
                        grades for that term. SF9 and SP-10 use the same
                        saved ratings.
                    </p>

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

                            <select
                                v-model="selectedGradeTerm"
                                class="filter-select"
                                :class="{
                                    'term-required': !canEncodeGrades,
                                }"
                            >
                                <option value="">Select Term</option>
                                <option value="1">Term 1</option>
                                <option value="2">Term 2</option>
                                <option value="3">Term 3</option>
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

                            <button
                                type="button"
                                class="voice-mode-toggle"
                                :class="{
                                    active: voiceModeEnabled,
                                    listening: voiceModeEnabled,
                                }"
                                :disabled="!canEncodeGrades"
                                @click="toggleVoiceModeGlobal"
                                :title="
                                    canEncodeGrades
                                        ? 'Start or stop voice grade entry (F2)'
                                        : 'Select a term first'
                                "
                            >
                                <Mic v-if="!voiceModeEnabled" :size="18" />
                                <MicOff v-else :size="18" />
                                <span>{{
                                    voiceModeEnabled
                                        ? "Stop Voice Input"
                                        : "Input Grades via Voice"
                                }}</span>
                                <kbd class="shortcut-key">F2</kbd>
                            </button>
                        </div>
                    </div>

                    <p v-if="!canEncodeGrades" class="term-lock-note">
                        Select a term above before encoding grades.
                    </p>

                    <div
                        v-if="voiceModeEnabled && !showGradeModal"
                        class="table-voice-bar listening-banner"
                    >
                        <div class="listening-main">
                            <div class="voice-indicator listening">
                                <span class="pulse-rings"></span>
                                <span class="pulse-dot"></span>
                                <Mic :size="20" class="mic-icon-active" />
                            </div>
                            <div class="listening-copy">
                                <strong>The system is listening</strong>
                                <span class="table-voice-text">{{
                                    tableVoiceStatus
                                }}</span>
                            </div>
                        </div>
                        <div
                            v-if="voiceTranscript"
                            class="table-voice-transcript"
                        >
                            Heard {{ voiceTranscript }}
                        </div>
                        <div class="table-voice-commands">
                            <span class="command-tag">1. Say the name</span>
                            <span class="command-tag"
                                >2. Wait for confirmation</span
                            >
                            <span class="command-tag">3. Say the grade</span>
                            <span class="command-tag"
                                >Only names &amp; grades are accepted</span
                            >
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
                                        <th
                                            :class="{
                                                'active-term-col':
                                                    selectedTermNumber === 1,
                                            }"
                                        >
                                            T1
                                        </th>
                                        <th
                                            :class="{
                                                'active-term-col':
                                                    selectedTermNumber === 2,
                                            }"
                                        >
                                            T2
                                        </th>
                                        <th
                                            :class="{
                                                'active-term-col':
                                                    selectedTermNumber === 3,
                                            }"
                                        >
                                            T3
                                        </th>
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
                                                    :disabled="!canEncodeGrades"
                                                    @click="
                                                        openGradeModal(grade)
                                                    "
                                                    :title="
                                                        canEncodeGrades
                                                            ? `Edit Term ${selectedTermNumber}`
                                                            : 'Select a term first'
                                                    "
                                                >
                                                    <Pencil :size="16" />
                                                </button>
                                                <Link
                                                    v-if="grade.student?.id"
                                                    class="action-btn form-link"
                                                    :href="`/teacher/students/${grade.student.id}/sf9`"
                                                    title="Open SF9 report card"
                                                >
                                                    SF9
                                                </Link>
                                                <Link
                                                    v-if="grade.student?.id"
                                                    class="action-btn form-link"
                                                    :href="`/teacher/students/${grade.student.id}/sf10`"
                                                    title="Open SP-10 permanent record"
                                                >
                                                    SP10
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredGrades.length === 0">
                                        <td colspan="9" class="empty-state">
                                            <div class="empty-state-content">
                                                <p v-if="studentGrades.length === 0">
                                                    No students are assigned to
                                                    you for
                                                    <strong
                                                        >SY
                                                        {{ currentSchoolYear }}</strong
                                                    >. Ask the administrator to
                                                    set up your teaching
                                                    assignments (Admin → Teacher
                                                    Assignments) so learners
                                                    for this school year
                                                    appear here.
                                                </p>
                                                <p v-else>
                                                    No grades match the
                                                    selected filters.
                                                </p>
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
                    <div class="modal-container grade-modal-clean">
                        <div class="grade-clean-header">
                            <h3>
                                <ClipboardList :size="20" />
                                Grade Entry
                            </h3>
                            <button
                                class="grade-clean-close"
                                @click="closeGradeModal"
                            >
                                <X :size="18" />
                            </button>
                        </div>

                        <div class="grade-clean-body">
                            <div class="grade-detail-header">
                                <div class="grade-detail-avatar">
                                    <img
                                        v-if="
                                            selectedGrade?.student
                                                ?.profile_photo
                                        "
                                        :src="`/storage/${selectedGrade.student.profile_photo}`"
                                        alt="Profile"
                                    />
                                    <span v-else>
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
                                <div class="grade-detail-title">
                                    <h4>
                                        {{
                                            selectedGrade?.student?.last_name
                                        }},
                                        {{
                                            selectedGrade?.student?.first_name
                                        }}
                                    </h4>
                                    <span
                                        v-if="selectedTermNumber"
                                        class="grade-term-badge"
                                    >
                                        Encoding Term {{ selectedTermNumber }}
                                    </span>
                                    <span
                                        v-if="selectedGrade?.student?.lrn"
                                        class="grade-lrn-badge"
                                    >
                                        LRN: {{ selectedGrade.student.lrn }}
                                    </span>
                                </div>
                            </div>

                            <div class="grade-detail-grid">
                                <div class="grade-detail-item full-width">
                                    <label>Subject</label>
                                    <span>
                                        {{
                                            selectedGrade?.subject?.name || "—"
                                        }}
                                    </span>
                                </div>
                                <div class="grade-detail-item">
                                    <label>Section</label>
                                    <span>
                                        {{
                                            selectedGrade?.section?.name || "—"
                                        }}
                                    </span>
                                </div>
                                <div class="grade-detail-item">
                                    <label>Final Grade</label>
                                    <span
                                        v-if="computedFinalGrade"
                                        class="grade-value-box"
                                        :class="
                                            getGradeClass(computedFinalGrade)
                                        "
                                    >
                                        {{ computedFinalGrade }}
                                    </span>
                                    <span v-else class="grade-muted">—</span>
                                </div>

                                <div
                                    class="grade-detail-item"
                                    :class="{
                                        'active-term':
                                            selectedTermNumber === 1,
                                        'locked-term':
                                            selectedTermNumber !== 1,
                                    }"
                                >
                                    <label>1st Term</label>
                                    <input
                                        id="voice-term-1-input"
                                        v-model="gradeForm.term_1"
                                        type="number"
                                        min="60"
                                        max="100"
                                        step="0.01"
                                        placeholder="—"
                                        class="grade-term-input"
                                        :disabled="selectedTermNumber !== 1"
                                    />
                                    <span
                                        v-if="gradeForm.term_1"
                                        class="grade-term-status"
                                        :class="getGradeClass(gradeForm.term_1)"
                                    >
                                        {{ getGradeLabel(gradeForm.term_1) }}
                                    </span>
                                    <span v-else class="grade-muted">
                                        Not Set
                                    </span>
                                </div>
                                <div
                                    class="grade-detail-item"
                                    :class="{
                                        'active-term':
                                            selectedTermNumber === 2,
                                        'locked-term':
                                            selectedTermNumber !== 2,
                                    }"
                                >
                                    <label>2nd Term</label>
                                    <input
                                        id="voice-term-2-input"
                                        v-model="gradeForm.term_2"
                                        type="number"
                                        min="60"
                                        max="100"
                                        step="0.01"
                                        placeholder="—"
                                        class="grade-term-input"
                                        :disabled="selectedTermNumber !== 2"
                                    />
                                    <span
                                        v-if="gradeForm.term_2"
                                        class="grade-term-status"
                                        :class="getGradeClass(gradeForm.term_2)"
                                    >
                                        {{ getGradeLabel(gradeForm.term_2) }}
                                    </span>
                                    <span v-else class="grade-muted">
                                        Not Set
                                    </span>
                                </div>
                                <div
                                    class="grade-detail-item"
                                    :class="{
                                        'active-term':
                                            selectedTermNumber === 3,
                                        'locked-term':
                                            selectedTermNumber !== 3,
                                    }"
                                >
                                    <label>3rd Term</label>
                                    <input
                                        id="voice-term-3-input"
                                        v-model="gradeForm.term_3"
                                        type="number"
                                        min="60"
                                        max="100"
                                        step="0.01"
                                        placeholder="—"
                                        class="grade-term-input"
                                        :disabled="selectedTermNumber !== 3"
                                    />
                                    <span
                                        v-if="gradeForm.term_3"
                                        class="grade-term-status"
                                        :class="getGradeClass(gradeForm.term_3)"
                                    >
                                        {{ getGradeLabel(gradeForm.term_3) }}
                                    </span>
                                    <span v-else class="grade-muted">
                                        Not Set
                                    </span>
                                </div>
                            </div>
                            <p class="grade-once-inline">
                                Saving once updates both SF9 and SP-10 for this
                                learner.
                                <Link
                                    v-if="selectedGrade?.student?.id"
                                    :href="`/teacher/students/${selectedGrade.student.id}/sf9`"
                                >
                                    Open SF9
                                </Link>
                                ·
                                <Link
                                    v-if="selectedGrade?.student?.id"
                                    :href="`/teacher/students/${selectedGrade.student.id}/sf10`"
                                >
                                    Open SP-10
                                </Link>
                            </p>
                        </div>

                        <div class="grade-clean-footer">
                            <button
                                type="button"
                                class="grade-btn-secondary"
                                @click="closeGradeModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="grade-btn-primary"
                                :disabled="isSubmitting"
                                @click="submitGrades"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="16"
                                    class="spin"
                                />
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

    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import { router, Head, Link } from "@inertiajs/vue3";
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
    schoolHead: {
        type: String,
        default: "",
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
const selectedGradeTerm = ref("");
const canEncodeGrades = computed(() =>
    ["1", "2", "3"].includes(String(selectedGradeTerm.value)),
);
const selectedTermNumber = computed(() =>
    canEncodeGrades.value ? Number(selectedGradeTerm.value) : null,
);
const showGradeModal = ref(false);
const selectedGrade = ref(null);
const gradeForm = ref({
    term_1: null,
    term_2: null,
    term_3: null,
});

// Voice Recognition State (Groq Whisper via backend)
const voiceModeEnabled = ref(false); // Persistent toggle - stays on across edits
const isVoiceActive = ref(false);
const currentVoiceQuarter = ref(1);
const voiceTranscript = ref("");
const voiceStatus = ref("Voice mode off");

// Groq audio streaming internals
const groqStream = ref(null); // MediaStream from getUserMedia
const groqRecorder = ref(null); // current MediaRecorder
const groqActive = ref(false); // recording loop is running
let groqLastTranscript = ""; // last dispatched text to avoid duplicates
let groqLastTranscriptAt = 0; // ms timestamp of last dispatch
// After this many ms since a duplicate was seen, we allow re-processing.
// Keeps grade double-entry protection while letting the teacher retry a
// short command (like "edit") without waiting forever between attempts.
const GROQ_DEDUP_WINDOW_MS = 1200;
// Generation counter — every time we start/stop the stream, switch modes, or
// speak a TTS prompt we bump this. Each recorder cycle captures the current
// value; if a transcript comes back with a stale value we drop it. This
// prevents late uploads (e.g. from student-select mode) from being treated as
// grade-modal input, and prevents the app from hearing its own voice prompts.
let groqGeneration = 0;
// Which command pipeline is currently active: "modal" (grade entry) or
// "table" (student selection). Captured per-recorder-cycle so a late
// transcript is only dispatched to the pipeline that was active when the
// audio was recorded.
let currentVoiceMode = "table";

// Track last announced term so the TTS only fires on actual changes.
let lastSpokenTerm = 1;

// Speak the term name whenever the highlighted term changes while the grade
// modal is open. This makes the flow feel like a two-way conversation.
watch(currentVoiceQuarter, (newTerm) => {
    if (!showGradeModal.value) return;
    if (!voiceModeEnabled.value) return;
    if (newTerm === lastSpokenTerm) return;
    lastSpokenTerm = newTerm;
    const phrases = { 1: "Term one", 2: "Term two", 3: "Term three" };
    speakPrompt(phrases[newTerm] || `Term ${newTerm}`, { wait: false });
});

// Table Voice Mode State
const tableVoiceActive = ref(false);
const focusedGradeRow = ref(null); // The grade row currently focused by voice
const tableVoiceStatus = ref("Say a student name to select...");
const voiceTableSaving = ref(false);

// Advisory Search/Filter State
const advisorySearch = ref("");
const advisoryGenderFilter = ref("all");

// Student Modal State
const showStudentModal = ref(false);
const selectedStudent = ref(null);

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

    // Filter unfinished grades. If a term is selected, only that term.
    if (showUnfinishedOnly.value) {
        filtered = filtered.filter((g) => {
            if (selectedTermNumber.value === 1) return !g.term_1;
            if (selectedTermNumber.value === 2) return !g.term_2;
            if (selectedTermNumber.value === 3) return !g.term_3;
            return !g.term_1 || !g.term_2 || !g.term_3 || !g.final_grade;
        });
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
    const map = new Map();
    const advisoryYear = props.advisorySection?.year_level;
    if (advisoryYear) {
        map.set(advisoryYear.id || props.advisorySection.year_level_id, {
            id: advisoryYear.id || props.advisorySection.year_level_id,
            name: advisoryYear.name,
        });
    }

    (props.teacherSections || []).forEach((section) => {
        const yearLevel = section.year_level;
        const id = yearLevel?.id || section.year_level_id;
        if (id && !map.has(id)) {
            map.set(id, {
                id,
                name: yearLevel?.name || section.name,
            });
        }
    });

    return [...map.values()];
});

const schoolFormStudents = computed(() => {
    const map = new Map();
    const advisoryYearLevelId =
        props.advisorySection?.year_level_id ||
        props.advisorySection?.year_level?.id;
    const advisoryYearLevelName = props.advisorySection?.year_level?.name;

    (props.advisoryStudents || []).forEach((student) => {
        map.set(student.id, {
            ...student,
            year_level_id: student.year_level_id || advisoryYearLevelId,
            year_level: student.year_level || advisoryYearLevelName,
        });
    });

    (props.studentGrades || []).forEach((grade) => {
        const student = grade.student;
        if (!student?.id || map.has(student.id)) {
            return;
        }

        map.set(student.id, {
            ...student,
            year_level_id:
                grade.section?.year_level_id || grade.section?.year_level?.id,
            year_level: grade.section?.year_level?.name,
        });
    });

    return [...map.values()];
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

const studentSpokenName = (student) => {
    const first = (student?.first_name || "").toString().trim();
    const last = (student?.last_name || "").toString().trim();
    return [first, last].filter(Boolean).join(" ");
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
    if (!canEncodeGrades.value) {
        toast.error("Select a term first before encoding grades.");
        return;
    }

    selectedGrade.value = grade;
    gradeForm.value = {
        term_1: grade.term_1,
        term_2: grade.term_2,
        term_3: grade.term_3,
    };
    showGradeModal.value = true;

    // Manual entry only. Pause table listening while the teacher types.
    currentVoiceMode = "table";
    groqGeneration++;
    clearPendingTens();
    cancelSpeech();
    stopGroqStream();
    tableVoiceActive.value = false;
};

const closeGradeModal = () => {
    // Pause modal voice recognition but keep mode enabled
    pauseVoiceRecognition();

    showGradeModal.value = false;
    selectedGrade.value = null;
    gradeForm.value = { term_1: null, term_2: null, term_3: null };

    // Flip back to table before restarting so any late modal transcripts
    // get dropped as cross-mode.
    currentVoiceMode = "table";
    groqGeneration++;

    if (voiceModeEnabled.value) {
        setTimeout(() => {
            startTableVoiceRecognition({ quiet: true });
        }, 100);
    }
};

// Text-to-Speech helper: makes the app "ask first" so voice mode feels
// conversational. Returns a Promise that resolves when speech finishes so we
// can wait before letting the mic listen again.
//
// While the app is speaking we prevent Whisper from hearing our own prompt
// with three coordinated moves:
//   1. Mute the microphone track so the recorder captures silence.
//   2. Force-cut the current recorder cycle at TTS start so any pre-TTS
//      speech is finalized before the mute takes effect.
//   3. Force-cut again at TTS end so post-TTS audio starts in a FRESH
//      recorder cycle. If we didn't do this, the recorder that was
//      already running during TTS would carry a large silent tail into
//      the post-TTS "edit" utterance, making Groq slow AND the cycle's
//      generation stamp would go stale if we bumped groqGeneration here.
let ttsSpeaking = false;

// Force-cut helper used by speakPrompt to boundary TTS. Stops the current
// recorder if any — the recorder cycle's own onstop handler will schedule a
// new cycle so we never leave the stream without an active recorder.
const forceCutRecorder = () => {
    const rec = groqRecorder.value;
    if (!rec) return;
    try {
        if (rec.state === "recording") rec.stop();
    } catch (e) {}
};

const speakPrompt = (text, { wait = true } = {}) => {
    if (typeof window === "undefined" || !("speechSynthesis" in window)) {
        return Promise.resolve();
    }

    // Mute mic tracks so Whisper cannot hear the prompt.
    const tracks = groqStream.value ? groqStream.value.getAudioTracks() : [];
    tracks.forEach((t) => (t.enabled = false));
    ttsSpeaking = true;
    // Cut the current recorder cycle so pre-TTS speech is finalized
    // immediately. A fresh cycle will start (and record muted silence).
    forceCutRecorder();

    let cleaned = false;
    const cleanup = () => {
        if (cleaned) return;
        cleaned = true;
        ttsSpeaking = false;
        tracks.forEach((t) => (t.enabled = true));
        // Cut the (TTS-time, mostly-silent) recorder cycle so the very next
        // thing the user says is captured in a fresh cycle. We deliberately
        // do NOT bump groqGeneration here — doing so would invalidate the
        // post-TTS transcript of the recorder cycle that captures the
        // user's next utterance.
        forceCutRecorder();
    };

    const speechDone = new Promise((resolve) => {
        try {
            window.speechSynthesis.cancel();
            const utter = new SpeechSynthesisUtterance(text);
            utter.lang = "en-US";
            utter.rate = 1.05;
            utter.pitch = 1.0;
            utter.volume = 1.0;
            let done = false;
            const finish = () => {
                if (done) return;
                done = true;
                cleanup();
                resolve();
            };
            utter.onend = finish;
            utter.onerror = finish;
            // Safety timeout in case onend never fires (some Chrome bug).
            setTimeout(finish, 8000);
            window.speechSynthesis.speak(utter);
        } catch (e) {
            cleanup();
            resolve();
        }
    });

    return wait ? speechDone : Promise.resolve();
};

const cancelSpeech = () => {
    try {
        if (typeof window !== "undefined" && "speechSynthesis" in window) {
            window.speechSynthesis.cancel();
        }
    } catch (e) {}
    // If cancel() was called mid-speech, force mic tracks back on so the
    // stream doesn't stay muted.
    if (ttsSpeaking) {
        ttsSpeaking = false;
        if (groqStream.value) {
            groqStream.value
                .getAudioTracks()
                .forEach((t) => (t.enabled = true));
        }
        forceCutRecorder();
    }
};

// Voice Recognition Functions (Groq Whisper backed with VAD)
//
// We keep a MediaRecorder always running and use a Web Audio AnalyserNode to
// detect when the teacher is speaking. As soon as we see a sustained pause
// after speech, we cut the current recording, ship the complete utterance to
// Groq, and immediately start recording again. This gives Whisper a full
// phrase for context, which dramatically improves accuracy compared with
// fixed-length chunks.

const MIN_UPLOAD_BYTES = 2000; // skip nearly-empty blobs
// RMS of int8 samples that counts as speech.
//   - Command/grade mode: 8 (very sensitive because user is prompted).
//   - Table-name mode: 14 (stricter — a full name should be spoken clearly;
//     this cuts down on background hum / air-con / typing tripping Whisper
//     into hallucinating a random name).
const VAD_SPEECH_THRESHOLD_CMD = 8;
const VAD_SPEECH_THRESHOLD_NAME = 14;
// Silence-after-speech timeout. Kept low in grade-entry mode where the
// teacher is saying a single two-digit number so the recorder cuts almost
// as soon as the number is finished. Longer in table mode because a full
// Filipino name can have a natural pause between first and last name.
// 500ms still keeps "eighty five" in one utterance while shaving ~150ms
// off the perceived response time per grade.
const VAD_SILENCE_MS_GRADE = 500;
const VAD_SILENCE_MS_CMD = 220; // next / save / edit — cut as soon as the word ends
const VAD_SILENCE_MS_NAME = 650;
const VAD_MIN_SPEECH_MS_CMD = 90; // grade / focused-command mode
const VAD_MIN_SPEECH_MS_NAME = 320; // full-name utterance in table mode
const VAD_MAX_UTTERANCE_MS = 5000; // hard cap per utterance
const VAD_MAX_IDLE_MS = 4000; // recycle recorder every N ms if no speech
const VAD_TICK_MS = 40;

const isNameListenMode = () =>
    currentVoiceMode === "table" && !focusedGradeRow.value;

const currentSilenceMs = () => {
    // After a grade is already in the current term, the next utterance is
    // almost always "next" / "back" / "save" — a single short word. Use a
    // tight silence window so the highlight moves immediately.
    if (currentVoiceMode === "modal") {
        const termKey = `term_${currentVoiceQuarter.value}`;
        const filled = gradeForm.value?.[termKey] != null;
        return filled ? VAD_SILENCE_MS_CMD : VAD_SILENCE_MS_GRADE;
    }
    if (focusedGradeRow.value) return VAD_SILENCE_MS_GRADE;
    return VAD_SILENCE_MS_NAME;
};

const currentSpeechThreshold = () =>
    isNameListenMode() ? VAD_SPEECH_THRESHOLD_NAME : VAD_SPEECH_THRESHOLD_CMD;

const currentMinSpeechMs = () =>
    isNameListenMode() ? VAD_MIN_SPEECH_MS_NAME : VAD_MIN_SPEECH_MS_CMD;

// Known Whisper "hallucinations" on silence / breath / music / short pops.
// Whisper's training data includes lots of YouTube subtitles, so on silence
// it commonly emits phrases like "thanks for watching", "please subscribe",
// "the end", "you", etc. We drop these outright so they never reach the
// name matcher and randomly select a student.
const GROQ_NOISE_TRANSCRIPTS = new Set([
    "you",
    "thank you",
    "thanks",
    "thanks for watching",
    "thank you for watching",
    "thank you very much",
    "thanks for listening",
    "please subscribe",
    "subscribe",
    "like and subscribe",
    "bye",
    "bye bye",
    "goodbye",
    "the end",
    "hmm",
    "hm",
    "uh",
    "um",
    "the",
    "a",
    "an",
    "and",
    "so",
    "is",
    "it",
    "it's",
    "its",
    "well",
    "oh",
    "ah",
    "eh",
    "huh",
    "tester",
    "test",
    "testing",
    "beep",
    "beep beep",
    "cheep",
    "cheep cheep",
    "la la",
    "la la la",
    "mm hmm",
    "mm",
    "yeah",
    "okay",
    "ok",
    "hello",
    "hi",
    "amen",
    "amara",
    "amara org",
    "www",
    ".",
    "",
]);

// Additional partial-match noise phrases (checked as substrings).
const GROQ_NOISE_SUBSTRINGS = [
    "beep",
    "cheep",
    "tester",
    "la la",
    "mm hmm",
    "amara",
    "subscribe",
    "thanks for watching",
    "thank you for watching",
];

// Internal VAD state (module-scoped so multiple invocations reuse)
let vadAudioCtx = null;
let vadAnalyser = null;
let vadRafHandle = null;

const getCsrfToken = () => {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute("content") || "" : "";
};

const pickAudioMime = () => {
    const candidates = [
        "audio/webm;codecs=opus",
        "audio/webm",
        "audio/ogg;codecs=opus",
        "audio/mp4",
    ];
    if (typeof MediaRecorder === "undefined") return "";
    for (const m of candidates) {
        try {
            if (MediaRecorder.isTypeSupported(m)) return m;
        } catch (e) {}
    }
    return "";
};

const stripPunctuation = (s) =>
    (s || "")
        .toLowerCase()
        .replace(/[.,!?;:"'()\[\]]/g, " ")
        .replace(/\s+/g, " ")
        .trim();

// Build a prompt hint that biases Whisper toward the vocabulary we expect.
// Whisper's `prompt` is limited to ~224 tokens (~1000 chars); cap length.
const PROMPT_MAX_CHARS = 900;
const buildTranscriptionPrompt = () => {
    // Two very different prompts: numeric grade mode vs. student-name mode.
    if (showGradeModal.value || focusedGradeRow.value) {
        return "85. 90. 75. 88. 92. 80. 95. 70. 78. 100. next. cancel.";
    }

    const baseNames =
        "The teacher will say a student's Filipino name (first and last name). Also possible commands: next, cancel.";
    try {
        const seen = new Set();
        const names = [];
        for (const g of filteredGrades.value || []) {
            const f = (g.student?.first_name || "").trim();
            const l = (g.student?.last_name || "").trim();
            const full = `${f} ${l}`.trim();
            if (!full) continue;
            const key = full.toLowerCase();
            if (seen.has(key)) continue;
            seen.add(key);
            names.push(full);
        }
        if (names.length === 0) return baseNames;
        let hint = baseNames + " Names: ";
        const room = PROMPT_MAX_CHARS - hint.length - 1;
        let acc = "";
        for (const n of names) {
            const next = acc ? `${acc}, ${n}` : n;
            if (next.length > room) break;
            acc = next;
        }
        return acc ? `${hint}${acc}.` : baseNames;
    } catch (e) {
        return baseNames;
    }
};

const uploadAudioChunk = async (blob, recordedMode = null) => {
    if (!blob || blob.size < MIN_UPLOAD_BYTES) return null;
    // Tell the backend whether this chunk is a grade digit or a student
    // name so it can pick the fastest suitable Whisper model. Use the mode
    // that was active when the audio was RECORDED, not the current one.
    // If a student is already focused we're waiting for a short command
    // ("edit"/"cancel"), so treat it like a grade chunk and use turbo.
    const effectiveMode = recordedMode || currentVoiceMode;
    const modeForServer =
        effectiveMode === "modal" || focusedGradeRow.value ? "grade" : "name";
    const fd = new FormData();
    fd.append("audio", blob, "chunk.webm");
    fd.append("language", "en");
    fd.append("prompt", buildTranscriptionPrompt());
    fd.append("mode", modeForServer);
    try {
        const res = await fetch("/teacher/transcribe", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-CSRF-TOKEN": getCsrfToken(),
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: fd,
        });
        if (!res.ok) return null;
        const data = await res.json();
        return data && typeof data.text === "string" ? data.text : null;
    } catch (e) {
        console.error("Groq transcription request failed", e);
        return null;
    }
};

const startGroqStream = async () => {
    if (groqActive.value) return true;
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        toast.error("Microphone is not available in this browser.");
        return false;
    }
    try {
        groqStream.value = await navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
            },
        });
        // If TTS is already playing (we start the mic during the prompt so
        // it is hot the instant speech ends), mute immediately so Whisper
        // does not transcribe our own voice.
        if (ttsSpeaking) {
            groqStream.value.getAudioTracks().forEach((t) => (t.enabled = false));
        }
    } catch (e) {
        console.error("Microphone access failed", e);
        if (showGradeModal.value) {
            voiceStatus.value = "Microphone access denied.";
        } else {
            tableVoiceStatus.value = "Microphone access denied.";
        }
        return false;
    }

    // Set up Web Audio analyser for VAD
    try {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        vadAudioCtx = new Ctx();
        if (vadAudioCtx.state === "suspended") {
            // Chrome sometimes creates the context in a suspended state
            try {
                await vadAudioCtx.resume();
            } catch (e) {}
        }
        const source = vadAudioCtx.createMediaStreamSource(groqStream.value);
        vadAnalyser = vadAudioCtx.createAnalyser();
        vadAnalyser.fftSize = 512;
        vadAnalyser.smoothingTimeConstant = 0.3;
        source.connect(vadAnalyser);
        // Keep the graph "live" without producing sound by piping through a
        // muted gain node into the destination. Some browsers require the
        // graph to reach the destination for the analyser to receive data.
        const silentGain = vadAudioCtx.createGain();
        silentGain.gain.value = 0;
        vadAnalyser.connect(silentGain);
        silentGain.connect(vadAudioCtx.destination);
    } catch (e) {
        console.error("Failed to init AudioContext", e);
    }

    groqActive.value = true;
    const mime = pickAudioMime();
    const analyserBuffer = vadAnalyser
        ? new Uint8Array(vadAnalyser.fftSize)
        : null;

    // State that resets each utterance
    let chunks = [];
    let recorder = null;
    let recordingStartedAt = 0;
    let firstSpeechAt = 0;
    let lastSpeechAt = 0;
    let speechDetected = false;
    let stopping = false;

    const startRecorder = () => {
        if (!groqActive.value || !groqStream.value) return;
        try {
            recorder = mime
                ? new MediaRecorder(groqStream.value, { mimeType: mime })
                : new MediaRecorder(groqStream.value);
        } catch (e) {
            console.error("MediaRecorder init failed", e);
            groqActive.value = false;
            return;
        }
        groqRecorder.value = recorder;
        chunks = [];
        speechDetected = false;
        firstSpeechAt = 0;
        lastSpeechAt = 0;
        stopping = false;

        // Snapshot the app state that this recorder cycle "belongs to". When
        // the transcript comes back we make sure both are still current so
        // late results don't leak across mode switches or TTS prompts.
        const myGen = groqGeneration;
        const myMode = currentVoiceMode;

        recorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) chunks.push(e.data);
        };
        recorder.onstop = async () => {
            const hadSpeech = speechDetected;
            const speechDuration =
                firstSpeechAt && lastSpeechAt
                    ? lastSpeechAt - firstSpeechAt
                    : 0;
            const blobType = recorder.mimeType || mime || "audio/webm";
            const blob = new Blob(chunks, { type: blobType });

            // Immediately start next recorder so we don't miss the next word
            if (groqActive.value) {
                setTimeout(startRecorder, 0);
            }

            if (!hadSpeech || speechDuration < currentMinSpeechMs()) return;
            if (blob.size < MIN_UPLOAD_BYTES) return;

            // Cheap guard: if the app started speaking / switched modes while
            // we were still holding the blob, skip the upload entirely.
            if (myGen !== groqGeneration) return;

            // Instant feedback while we wait for Whisper — otherwise the
            // teacher sees no change for ~500–1500 ms and thinks the mic is
            // frozen.
            if (
                myMode === "table" &&
                focusedGradeRow.value &&
                !voiceTableSaving.value
            ) {
                tableVoiceStatus.value = "Heard you — transcribing...";
            }

            const text = await uploadAudioChunk(blob, myMode);
            if (!text) return;
            // Late-arrival guard: state may have changed while the request
            // was in flight (mode switch, TTS spoke, stream stopped, etc.).
            if (myGen !== groqGeneration) {
                if (typeof console !== "undefined") {
                    console.log(
                        "[voice] dropped stale transcript:",
                        text,
                        "(gen mismatch)",
                    );
                }
                return;
            }
            if (myMode !== currentVoiceMode) {
                if (typeof console !== "undefined") {
                    console.log(
                        "[voice] dropped cross-mode transcript:",
                        text,
                        `(recorded as ${myMode}, now ${currentVoiceMode})`,
                    );
                }
                return;
            }
            const clean = stripPunctuation(text);
            if (!clean || GROQ_NOISE_TRANSCRIPTS.has(clean)) return;
            // Extra hallucination filter: if the transcript contains a
            // known Whisper garbage phrase, drop it.
            //   - In grade mode we only drop if there are also no digits.
            //   - In table mode we drop unconditionally, because we don't
            //     want a phrase like "thanks for watching" to fuzzy-match
            //     a student name on quiet-mic silence.
            if (
                GROQ_NOISE_SUBSTRINGS.some((sub) => clean.includes(sub)) &&
                (myMode !== "modal" || !/\d/.test(clean))
            ) {
                if (typeof console !== "undefined") {
                    console.log(
                        "[voice] dropped hallucination:",
                        clean,
                        "mode:",
                        myMode,
                    );
                }
                return;
            }
            // Duplicate guard: drop only if we saw the exact same text
            // recently. After GROQ_DEDUP_WINDOW_MS ms we allow it again so
            // a teacher can retry the same short command (e.g., "edit")
            // without having to wait indefinitely.
            const now = Date.now();
            if (
                clean === groqLastTranscript &&
                now - groqLastTranscriptAt < GROQ_DEDUP_WINDOW_MS
            ) {
                return;
            }
            groqLastTranscript = clean;
            groqLastTranscriptAt = now;
            dispatchGroqTranscript(clean, myMode);
        };

        try {
            recorder.start();
            recordingStartedAt = performance.now();
        } catch (e) {
            console.error("MediaRecorder start failed", e);
            groqActive.value = false;
        }
    };

    const cutHere = () => {
        if (stopping || !recorder) return;
        if (recorder.state !== "recording") return;
        stopping = true;
        try {
            recorder.stop();
        } catch (e) {
            stopping = false;
        }
    };

    const tick = () => {
        if (!groqActive.value) return;
        if (analyserBuffer && vadAnalyser) {
            // While the app is speaking a prompt, pretend the mic is silent.
            // This prevents the VAD from cutting on our own TTS echo and
            // wipes any partial "speech" state we captured before muting.
            if (ttsSpeaking) {
                speechDetected = false;
                firstSpeechAt = 0;
                lastSpeechAt = 0;
                recordingStartedAt = performance.now();
                vadRafHandle = setTimeout(tick, VAD_TICK_MS);
                return;
            }
            vadAnalyser.getByteTimeDomainData(analyserBuffer);
            // RMS around 128 baseline
            let sumSq = 0;
            for (let i = 0; i < analyserBuffer.length; i++) {
                const v = analyserBuffer[i] - 128;
                sumSq += v * v;
            }
            const rms = Math.sqrt(sumSq / analyserBuffer.length);
            const now = performance.now();
            const isSpeech = rms > currentSpeechThreshold();

            if (isSpeech) {
                if (!speechDetected) {
                    speechDetected = true;
                    firstSpeechAt = now;
                }
                lastSpeechAt = now;
            }

            const elapsed = now - recordingStartedAt;
            const silenceDur = lastSpeechAt ? now - lastSpeechAt : elapsed;

            const silenceCutoff = currentSilenceMs();
            if (
                speechDetected &&
                silenceDur >= silenceCutoff &&
                elapsed - silenceDur >= currentMinSpeechMs()
            ) {
                cutHere();
            } else if (elapsed >= VAD_MAX_UTTERANCE_MS) {
                cutHere();
            } else if (!speechDetected && elapsed >= VAD_MAX_IDLE_MS) {
                // No speech captured; recycle recorder to keep it healthy
                cutHere();
            }
        }
        vadRafHandle = setTimeout(tick, VAD_TICK_MS);
    };

    startRecorder();
    tick();
    return true;
};

const stopGroqStream = () => {
    groqActive.value = false;
    // Any in-flight upload from the previous cycle should be discarded when
    // it returns. Bumping the generation is enough — the onstop handler
    // compares against groqGeneration before dispatching.
    groqGeneration++;
    if (vadRafHandle) {
        clearTimeout(vadRafHandle);
        vadRafHandle = null;
    }
    if (groqRecorder.value) {
        try {
            if (groqRecorder.value.state === "recording") {
                groqRecorder.value.stop();
            }
        } catch (e) {}
        groqRecorder.value = null;
    }
    if (groqStream.value) {
        try {
            groqStream.value.getTracks().forEach((t) => t.stop());
        } catch (e) {}
        groqStream.value = null;
    }
    if (vadAudioCtx) {
        try {
            vadAudioCtx.close();
        } catch (e) {}
        vadAudioCtx = null;
    }
    vadAnalyser = null;
    groqLastTranscript = "";
};

const dispatchGroqTranscript = (text, recordedMode = null) => {
    // Helpful for debugging: shows exactly what Whisper heard.
    // Open DevTools > Console to see the raw transcripts while you test.
    if (typeof console !== "undefined") {
        console.log("[voice] heard:", text, "mode:", recordedMode);
    }

    // Route based on the mode that was active when the audio was recorded,
    // not the current mode. This is critical: without it, a chunk captured
    // during student selection could be dispatched into the grade modal after
    // the user clicks a row, and vice versa.
    const routeMode = recordedMode || currentVoiceMode;

    if (routeMode === "modal") {
        // Sanity check — if the modal is somehow closed by the time we
        // dispatch, drop instead of falling through to table mode.
        if (!showGradeModal.value) return;
        processGroqModalTranscript(text);
        return;
    }

    // Table (student selection) mode
    if (showGradeModal.value) return; // guard: modal took over while we waited
    voiceTranscript.value = `"${text}"`;
    const processed = processTableVoiceCommand(text, true);
    if (processed) {
        setTimeout(() => {
            voiceTranscript.value = "";
        }, 800);
    }
    // If we didn't match, keep the transcript on screen so the teacher
    // can see what was heard and adjust.
};

const WORD_ONES = {
    zero: 0,
    oh: 0,
    o: 0,
    one: 1,
    won: 1,
    wan: 1,
    two: 2,
    to: 2,
    too: 2,
    three: 3,
    tree: 3,
    free: 3,
    four: 4,
    for: 4,
    five: 5,
    fife: 5,
    six: 6,
    seven: 7,
    eight: 8,
    ate: 8,
    nine: 9,
    niner: 9,
};

const WORD_TENS = {
    sixty: 60,
    seventy: 70,
    eighty: 80,
    aighty: 80,
    ninty: 90,
    ninety: 90,
};

const WORD_TEENS = {
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
};

// "eighty" then a pause then "five" is a common VAD split. Hold the tens
// digit briefly so the ones digit can complete the grade.
let pendingTens = null;
let pendingTensTimer = null;

const clearPendingTens = () => {
    pendingTens = null;
    if (pendingTensTimer) {
        clearTimeout(pendingTensTimer);
        pendingTensTimer = null;
    }
};

const applyGradeToCurrentTerm = (grade) => {
    clearPendingTens();
    const term = currentVoiceQuarter.value;
    gradeForm.value[`term_${term}`] = grade;
    if (term < 3) {
        showStatus(`Term ${term} = ${grade} — say next`);
    } else {
        showStatus(`Term 3 = ${grade} — say save`);
    }
};

const extractOnesDigit = (rawText) => {
    if (!rawText) return null;
    const text = rawText
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    if (!text) return null;
    const spoken = parseSpokenNumber(text);
    if (spoken !== null && spoken >= 0 && spoken <= 9) return spoken;
    const m = text.match(/\b(\d)\b/);
    if (m) {
        const n = parseInt(m[1], 10);
        if (n >= 0 && n <= 9) return n;
    }
    const words = text.split(/\s+/);
    for (const w of words) {
        if (WORD_ONES[w] !== undefined) return WORD_ONES[w];
    }
    return null;
};

// Best-effort extraction of a grade (60-100) from a messy Whisper transcript.
// Handles cases like "the b-8", "eighty five", "the 85 point", "it's 90",
// "one hundred", "80.", "e.g. 88", etc.
const extractGrade = (rawText) => {
    if (!rawText) return null;

    // Normalize: lowercase, drop punctuation, collapse whitespace.
    let text = rawText
        .toLowerCase()
        .replace(/[^a-z0-9\s]/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    if (!text) return null;

    // Common Whisper garbage prefixes / filler
    const NOISE = new Set([
        "the",
        "a",
        "an",
        "uh",
        "um",
        "hmm",
        "so",
        "ok",
        "okay",
        "is",
        "it",
        "s",
        "that",
        "this",
        "point",
        "grade",
        "percent",
        "percentage",
    ]);
    const tokens = text.split(" ").filter((t) => t && !NOISE.has(t));
    if (tokens.length === 0) return null;
    text = tokens.join(" ");

    // 1) Direct digit sequences in valid range (prefer first match)
    const digitMatches = text.match(/\d{2,3}/g);
    if (digitMatches) {
        for (const m of digitMatches) {
            const n = parseInt(m, 10);
            if (n >= 60 && n <= 100) return n;
        }
    }

    // 2) Two isolated digits ("8 5" / "8  5") → 85
    const singleDigits = text.match(/\b\d\b/g);
    if (singleDigits && singleDigits.length >= 2) {
        const n = parseInt(singleDigits[0] + singleDigits[1], 10);
        if (n >= 60 && n <= 100) return n;
    }

    // 3) Spoken tens + ones in the same utterance ("eighty five")
    const words = text.split(/\s+/);
    for (let i = 0; i < words.length; i++) {
        const tens = WORD_TENS[words[i]];
        if (tens === undefined) continue;
        const onesWord = words[i + 1];
        if (onesWord && WORD_ONES[onesWord] !== undefined) {
            return tens + WORD_ONES[onesWord];
        }
        const onesDigit = onesWord && /^\d$/.test(onesWord) ? parseInt(onesWord, 10) : null;
        if (onesDigit !== null && onesDigit >= 0 && onesDigit <= 9) {
            return tens + onesDigit;
        }
    }

    // 4) Try parseSpokenNumber on the whole cleaned text
    const spoken = parseSpokenNumber(text);
    if (spoken !== null && spoken >= 60 && spoken <= 100) return spoken;

    // 5) Fallback: reconstruct two-digit numbers from leftover digits
    const digitsOnly = text.replace(/[^0-9]/g, "");
    if (digitsOnly.length >= 2) {
        for (let len = 3; len >= 2; len--) {
            for (let i = 0; i + len <= digitsOnly.length; i++) {
                const n = parseInt(digitsOnly.slice(i, i + len), 10);
                if (n >= 60 && n <= 100) return n;
            }
        }
    }

    // 6) Letter-digit substitutions: Whisper occasionally writes a single
    //    digit as a letter (b/8, g/9, o/0).
    const substitutions = { b: "8", g: "9", o: "0", l: "1", s: "5", z: "2" };
    const normalizedTokens = text.split(" ").map((t) => {
        let out = "";
        for (const ch of t) {
            out += substitutions[ch] !== undefined ? substitutions[ch] : ch;
        }
        return out;
    });
    const rejoined = normalizedTokens.join("");
    const substMatches = rejoined.match(/\d{2,3}/g);
    if (substMatches) {
        for (const m of substMatches) {
            const n = parseInt(m, 10);
            if (n >= 60 && n <= 100) return n;
        }
    }

    return null;
};

const processGroqModalTranscript = (text) => {
    voiceTranscript.value = `"${text}"`;
    const words = text.split(/\s+/).filter(Boolean);
    if (words.length === 0) return;

    const clearTranscriptSoon = () => {
        setTimeout(() => {
            voiceTranscript.value = "";
        }, 800);
    };

    // 1) Two-word quarter/term jumps first (so "term one" wins over parsing
    //    "one" as a number)
    for (let i = 0; i < words.length - 1; i++) {
        const pair = `${words[i]} ${words[i + 1]}`;
        if (tryProcessCommand(words[i + 1], pair, true)) {
            clearPendingTens();
            clearTranscriptSoon();
            return;
        }
    }

    // 1b) next / back / save / clear BEFORE grade extraction so a leftover
    //     number in the transcript cannot steal the navigation command.
    for (let i = 0; i < words.length; i++) {
        if (
            isNextCommand(words[i]) ||
            isBackCommand(words[i]) ||
            isSaveCommand(words[i]) ||
            isClearCommand(words[i])
        ) {
            if (tryProcessCommand(words[i], words[i], true)) {
                clearPendingTens();
                clearTranscriptSoon();
                return;
            }
        }
    }

    // 2) Aggressive grade extraction (handles messy Whisper output)
    const grade = extractGrade(text);
    if (grade !== null) {
        // Bare tens (80) may be the first half of "eighty five". Wait a
        // beat for a ones digit before committing.
        if (grade === 60 || grade === 70 || grade === 80 || grade === 90) {
            clearPendingTens();
            pendingTens = grade;
            showStatus(`Heard ${grade}...`);
            pendingTensTimer = setTimeout(() => {
                if (pendingTens === grade) {
                    applyGradeToCurrentTerm(grade);
                }
            }, 900);
            clearTranscriptSoon();
            return;
        }
        applyGradeToCurrentTerm(grade);
        clearTranscriptSoon();
        return;
    }

    // 2b) Ones digit completing a pending tens ("five" after "eighty")
    if (pendingTens !== null) {
        const ones = extractOnesDigit(text);
        if (ones !== null) {
            applyGradeToCurrentTerm(pendingTens + ones);
            clearTranscriptSoon();
            return;
        }
    }

    // 3) Single-word commands (save / next / back / clear / edit / t1..t3)
    for (let i = words.length - 1; i >= 0; i--) {
        if (tryProcessCommand(words[i], words[i], true)) {
            clearPendingTens();
            clearTranscriptSoon();
            return;
        }
    }

    // 4) Nothing matched: keep the transcript on screen so the teacher can
    //    see what Whisper heard (helps them adjust their phrasing).
};

// Helper to show status and auto-clear after 1 second
let statusTimeout = null;
const showStatus = (message) => {
    voiceStatus.value = message;
    if (statusTimeout) clearTimeout(statusTimeout);
    statusTimeout = setTimeout(() => {
        voiceStatus.value = `Listening — Term ${currentVoiceQuarter.value}`;
    }, 800);
};

// Track last navigation command to prevent double-triggering
let lastNavCommand = "";
let lastNavTime = 0;

// Whisper mishears "save" as a huge variety of short S-words. Recognize any
// of them so the teacher can always submit by voice. The check has three
// layers:
//   1. An explicit whitelist covering the user-reported variants.
//   2. A regex covering the phonetic shape: starts with S, one vowel, and
//      an optional trailing consonant (v/f/b/m/y/p).
//   3. A Levenshtein distance <= 1 from "save" or "safe" for anything the
//      regex might miss (e.g. "sabe", "seef").
// The check is intentionally conservative on length (<= 5 chars) so it
// can't accidentally match longer real words.
const SAVE_EXACT_WORDS = new Set([
    "save",
    "saved",
    "saves",
    "safe",
    "safes",
    "sabe",
    "sav",
    "sef",
    "seif",
    "seef",
    "say",
    "says",
    "same",
    "sane",
    "sey",
    "seyb",
    "siy",
    "siyb",
    "sayb",
    "sub",
    "sup",
    "sib",
    "sob",
    "seb",
    "sabb",
    "sebb",
    "sep",
    "sap",
    "sav",
    "seyv",
    "seyf",
    "sayf",
    "sayv",
    // Whisper occasionally hears "save" as an affirmative or a C-word.
    // Teacher explicitly requested these variants — trade-off: a stray
    // "yes" from background chatter could auto-submit, but that only
    // happens while the grade modal is open and the mic is actively
    // listening for a command.
    "yes",
    "yeah",
    "yep",
    "ced",
    "cib",
    "seb",
    "sed",
    "cev",
    "seve",
    "seb",
]);

const NEXT_WORDS = new Set([
    "next",
    "nexts",
    "necks",
    "neks",
    "niks",
    "nix",
    "nicks",
    "nick",
    "text",
    "texts",
    "nest",
    "nests",
    "nx",
    "nks",
    "necs",
    "nyx",
]);

const BACK_WORDS = new Set(["back", "bag", "beck", "bak"]);
const CLEAR_WORDS = new Set(["clear", "claire", "klir", "kleer"]);

const isNextCommand = (word) => {
    if (!word) return false;
    const w = word.toLowerCase().replace(/[^a-z]/g, "");
    return NEXT_WORDS.has(w);
};

const isBackCommand = (word) => {
    if (!word) return false;
    const w = word.toLowerCase().replace(/[^a-z]/g, "");
    return BACK_WORDS.has(w);
};

const isClearCommand = (word) => {
    if (!word) return false;
    const w = word.toLowerCase().replace(/[^a-z]/g, "");
    return CLEAR_WORDS.has(w);
};

const goToTerm = (term) => {
    if (
        selectedTermNumber.value &&
        Number(term) !== selectedTermNumber.value
    ) {
        showStatus(`Encoding Term ${selectedTermNumber.value} only`);
        return;
    }

    const t = Math.min(3, Math.max(1, term));
    // Set lastSpokenTerm first so the watcher does not speak "Term two"
    // and mute the mic — that mute is what made "next" feel delayed.
    lastSpokenTerm = t;
    currentVoiceQuarter.value = t;
    showStatus(`Term ${t}`);
    const input = document.querySelector(`#voice-term-${t}-input`);
    if (input) input.focus();
};

const isSaveCommand = (word) => {
    if (!word) return false;
    const w = word.toLowerCase().replace(/[^a-z]/g, "");
    if (!w) return false;
    if (SAVE_EXACT_WORDS.has(w)) return true;
    // Shape check: short S-word ending in a save-family consonant
    // (v/f/b only — the actual consonants in "save"/"safe"). Requiring the
    // trailing consonant kills the false positives that came from common
    // English fillers Whisper hallucinates ("so", "sea", "see", "sam",
    // "sim", "some", "sum", "sap") which used to slip through when we
    // allowed m/y/p endings or no ending at all.
    if (w.length >= 3 && w.length <= 5 && /^s[aeiouy]{1,2}[vfb]e?$/.test(w)) {
        return true;
    }
    // Levenshtein fallback: distance 1 from "save" or "safe".
    if (levenshtein(w, "save") <= 1) return true;
    if (levenshtein(w, "safe") <= 1) return true;
    return false;
};

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
        if (lastNavCommand === twoWords && now - lastNavTime < 400) {
            return false;
        }
        lastNavCommand = twoWords;
        lastNavTime = now;
        goToTerm(quarterTwoWordPatterns[twoWords]);
        return true;
    }

    // Check if word is a grade number (60-100)
    const gradeNumber = parseSpokenNumber(word);
    if (gradeNumber !== null && gradeNumber >= 60 && gradeNumber <= 100) {
        applyGradeToCurrentTerm(gradeNumber);
        lastNavCommand = "";
        return true;
    }

    if (isNextCommand(word)) {
        if (lastNavCommand === "next" && now - lastNavTime < 400) {
            return false;
        }
        lastNavCommand = "next";
        lastNavTime = now;

        if (currentVoiceQuarter.value < 3) {
            goToTerm(currentVoiceQuarter.value + 1);
        } else {
            showStatus("Last term — say save");
        }
        return true;
    }

    if (isBackCommand(word)) {
        if (lastNavCommand === "back" && now - lastNavTime < 400) {
            return false;
        }
        lastNavCommand = "back";
        lastNavTime = now;

        if (currentVoiceQuarter.value > 1) {
            goToTerm(currentVoiceQuarter.value - 1);
        }
        return true;
    }

    if (isClearCommand(word)) {
        const termKey = `term_${currentVoiceQuarter.value}`;
        gradeForm.value[termKey] = null;
        showStatus(`Term ${currentVoiceQuarter.value} cleared`);
        return true;
    }

    if (isSaveCommand(word)) {
        voiceStatus.value = "Saving...";
        submitGrades();
        return true;
    }

    if (word === "t1" || word === "q1" || word === "queue1") {
        goToTerm(1);
        return true;
    }
    if (word === "t2" || word === "q2" || word === "queue2") {
        goToTerm(2);
        return true;
    }
    if (word === "t3" || word === "q3" || word === "queue3") {
        goToTerm(3);
        return true;
    }
    if (word === "q4" || word === "queue4") {
        goToTerm(3);
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
        aighty: 80,
        ninety: 90,
        ninty: 90,
        hundred: 100,
        ate: 8,
        fife: 5,
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
    if (!voiceModeEnabled.value && !canEncodeGrades.value) {
        toast.error("Select a term first before using voice grade entry.");
        return;
    }

    voiceModeEnabled.value = !voiceModeEnabled.value;

    if (voiceModeEnabled.value) {
        // Start table voice recognition when enabled from header
        startTableVoiceRecognition();
    } else {
        // Stop all voice recognition (Groq stream is shared)
        cancelSpeech();
        stopGroqStream();
        isVoiceActive.value = false;
        tableVoiceActive.value = false;
        voiceTranscript.value = "";
        voiceStatus.value = "Voice mode off";
        tableVoiceStatus.value = "Say a student name to select...";
        focusedGradeRow.value = null;
        tablePromptCount = 0;
        voiceGreetingSpoken = false;
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

const startVoiceRecognition = async () => {
    // Reuse the Groq audio stream. Transcripts recorded from now on are
    // stamped as "modal" so late-arriving results can be routed correctly.
    currentVoiceMode = "modal";
    const startTerm = selectedTermNumber.value || 1;
    currentVoiceQuarter.value = startTerm;
    lastSpokenTerm = startTerm;
    focusQuarterInput(startTerm);
    clearPendingTens();

    const studentName = studentSpokenName(selectedGrade.value?.student);
    const termWords = { 1: "one", 2: "two", 3: "three" };
    const prompt = studentName
        ? `${studentName}. Term ${termWords[startTerm]}.`
        : `Term ${termWords[startTerm]}.`;
    voiceStatus.value = studentName
        ? `Entering grades for ${studentName}`
        : `Listening — Term ${startTerm}`;

    // Start the mic BEFORE speaking so it is already open (muted) while
    // TTS plays. Previously we awaited TTS then called getUserMedia, and
    // the teacher's first grade was spoken into a dead mic.
    ttsSpeaking = true;
    const ok = await startGroqStream();
    if (!ok) {
        ttsSpeaking = false;
        voiceStatus.value = "Failed to start voice. Try again.";
        return;
    }
    isVoiceActive.value = true;
    await speakPrompt(prompt);
    voiceStatus.value = "Listening — say a grade or term";
};

// Fully disable voice mode
const stopVoiceRecognition = () => {
    cancelSpeech();
    stopGroqStream();
    isVoiceActive.value = false;
    voiceModeEnabled.value = false;
    voiceTranscript.value = "";
    voiceStatus.value = "Voice mode off";
};

// Pause voice (for modal close) without disabling mode
const pauseVoiceRecognition = () => {
    cancelSpeech();
    clearPendingTens();
    stopGroqStream();
    isVoiceActive.value = false;
    voiceTranscript.value = "";
};

// Table voice recognition (student selection) is powered by the same shared
// Groq audio stream. Transcripts are routed to processTableVoiceCommand when
// no grade modal is open.

// Whisper mishears "edit" as any of: "at it", "add it", "added", "adit",
// "edited", "editing", "it it", "eddie", "at ed", "att it". We accept a
// broad set of variants + a per-word fuzzy match so the teacher never has
// to repeat themselves to open the modal.
const EDIT_EXACT_WORDS = new Set([
    "edit",
    "edits",
    "edited",
    "editing",
    "adit",
    "adet",
    "aded",
    "added",
    "add",
    "adds",
    "adding",
    "eddie",
    "eddy",
    "editor",
    "audit",
    "add-it",
    "it-it",
    "atit",
    "addit",
    "attit",
    "eddit",
    "atid",
    "addid",
]);
const EDIT_PHRASES = [
    "edit",
    "at it",
    "add it",
    "at ed",
    "add ed",
    "it it",
    "att it",
    "ad it",
    "et it",
    "open",
    "enter",
];

const isEditCommand = (transcript) => {
    if (!transcript) return false;
    const t = transcript.toLowerCase();
    // Phrase substring check - catches multi-word mishearings
    for (const p of EDIT_PHRASES) {
        if (t.includes(p)) return true;
    }
    // Per-word exact / fuzzy check
    const words = t.replace(/[^a-z\s]/g, " ").split(/\s+/).filter(Boolean);
    for (const w of words) {
        if (EDIT_EXACT_WORDS.has(w)) return true;
        // Fuzzy: distance <= 1 from "edit" for anything short (<= 6 chars)
        if (w.length >= 3 && w.length <= 6 && levenshtein(w, "edit") <= 1) {
            return true;
        }
    }
    return false;
};

const processTableVoiceCommand = (transcript, isFinal) => {
    if (voiceTableSaving.value || ttsSpeaking) {
        return true;
    }

    // Strict mode: the voice pipeline only accepts two things —
    //   1. A numeric grade (60–100)  → saved to the currently focused row
    //   2. A student name            → jumps focus to that row
    // A name is ALWAYS honored — even mid-flow — so the teacher can go
    // back to a previously graded student and re-say the correct grade
    // when Whisper misheard (e.g. 95 → 90). Anything that is neither a
    // grade nor a matching name is silently ignored.

    if (focusedGradeRow.value) {
        const grade = extractGrade(transcript);
        if (grade !== null) {
            if (grade === 60 || grade === 70 || grade === 80 || grade === 90) {
                clearPendingTens();
                pendingTens = grade;
                tableVoiceStatus.value = `Heard ${grade}...`;
                pendingTensTimer = setTimeout(() => {
                    if (pendingTens === grade) {
                        saveVoiceTableGrade(focusedGradeRow.value, grade);
                    }
                }, 900);
                return true;
            }
            saveVoiceTableGrade(focusedGradeRow.value, grade);
            return true;
        }

        if (pendingTens !== null) {
            const ones = extractOnesDigit(transcript);
            if (ones !== null) {
                const combined = pendingTens + ones;
                clearPendingTens();
                if (combined >= 60 && combined <= 100) {
                    saveVoiceTableGrade(focusedGradeRow.value, combined);
                    return true;
                }
            }
        }

        // No grade in this utterance — try treating it as a student name
        // so the teacher can jump BACK to a previously graded student to
        // correct a mis-heard value.
        const jumpTarget = findStudentByVoice(transcript);
        if (
            jumpTarget &&
            (jumpTarget.student?.id !== focusedGradeRow.value.student?.id ||
                jumpTarget.subject_id !== focusedGradeRow.value.subject_id)
        ) {
            clearPendingTens();
            focusStudentForVoice(jumpTarget, {
                announce: true,
                confirm: true,
            });
            return true;
        }

        // Neither a grade nor a different student's name — ignore.
        if (typeof console !== "undefined") {
            console.log("[voice] ignored non-grade in focused mode:", transcript);
        }
        return true;
    }

    const matchedGrade = findStudentByVoice(transcript);
    if (matchedGrade) {
        focusStudentForVoice(matchedGrade, {
            announce: true,
            confirm: true,
        });
        return true;
    }

    // Not a name — ignore. findStudentByVoice already updates the status.
    return false;
};

const averageFinalGrade = (term1, term2, term3) => {
    const values = [term1, term2, term3]
        .map((value) => parseFloat(value))
        .filter((value) => !Number.isNaN(value) && value > 0);
    if (values.length === 0) return null;
    return (values.reduce((sum, value) => sum + value, 0) / values.length).toFixed(2);
};

const focusStudentForVoice = (
    grade,
    { announce = false, confirm = false } = {},
) => {
    focusedGradeRow.value = grade;
    scrollToFocusedRow(grade);
    clearPendingTens();
    const name = studentSpokenName(grade.student) || "this student";
    const term = selectedTermNumber.value || "";
    tableVoiceStatus.value = confirm
        ? `Confirmed ${name} — say the Term ${term} grade`
        : `The system is listening — say ${name}'s Term ${term} grade`;
    if (announce) {
        const phrase = confirm
            ? `${name}. Say the grade for term ${term}.`
            : `Next student: ${name}. Say the grade.`;
        speakPrompt(phrase, { wait: false });
    }
};

const waitForVoiceUiToSettle = async () => {
    await nextTick();
    await new Promise((resolve) => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => setTimeout(resolve, 220));
        });
    });
};

const announceNextVoiceStudent = async (value, nextRow) => {
    const term = selectedTermNumber.value || "";
    const nextName = studentSpokenName(nextRow.student) || "the next student";
    focusStudentForVoice(nextRow, { announce: false });
    tableVoiceStatus.value = `Saved ${value}. Next: ${nextName}`;
    // After the first student, only speak the next student's name. The
    // teacher already knows the pattern — echoing "say the grade" every
    // time gets repetitive and slows the flow.
    await speakPrompt(`${nextName}.`, { wait: true });
    if (voiceModeEnabled.value && focusedGradeRow.value) {
        tableVoiceStatus.value = `The system is listening — say ${nextName}'s Term ${term} grade`;
    }
};

const saveVoiceTableGrade = (row, value) => {
    if (voiceTableSaving.value || !row || !selectedTermNumber.value) return;

    const studentId = row.student?.id;
    const subjectId = row.subject_id;
    if (!studentId || !subjectId) return;

    voiceTableSaving.value = true;
    const term = selectedTermNumber.value;
    const payload = {
        term_1: term === 1 ? value : row.term_1,
        term_2: term === 2 ? value : row.term_2,
        term_3: term === 3 ? value : row.term_3,
        section_id: row.section_id,
    };
    payload.final_grade = averageFinalGrade(
        payload.term_1,
        payload.term_2,
        payload.term_3,
    );

    const name = studentSpokenName(row.student) || "Student";
    tableVoiceStatus.value = `Saving ${name} — Term ${term} = ${value}`;

    let saveSucceeded = false;

    router.put(`/teacher/grades/${studentId}/${subjectId}`, payload, {
        preserveState: true,
        preserveScroll: true,
        only: ["studentGrades"],
        showProgress: false,
        onSuccess: () => {
            saveSucceeded = true;
            toast.success(`${name}: Term ${term} = ${value}`);
        },
        onError: () => {
            toast.error("Could not save the grade. Say it again.");
            tableVoiceStatus.value = "Save failed. Say the grade again.";
        },
        onFinish: async () => {
            if (!saveSucceeded || !voiceModeEnabled.value) {
                voiceTableSaving.value = false;
                return;
            }

            await waitForVoiceUiToSettle();

            const nextHint = findNextIncompleteGrade(row);
            try {
                if (nextHint) {
                    const refreshed =
                        (filteredGrades.value || []).find(
                            (grade) =>
                                grade.student?.id === nextHint.student?.id &&
                                grade.subject_id === nextHint.subject_id,
                        ) || nextHint;
                    await announceNextVoiceStudent(value, refreshed);
                } else {
                    focusedGradeRow.value = null;
                    tableVoiceStatus.value =
                        "All students for this term are done. Say a name or stop voice input.";
                    await speakPrompt(
                        `${value}. All grades for this term are complete.`,
                        { wait: true },
                    );
                }
            } finally {
                voiceTableSaving.value = false;
            }
        },
    });
};

// Levenshtein distance (small strings only) for fuzzy name matching.
const levenshtein = (a, b) => {
    if (a === b) return 0;
    const al = a.length,
        bl = b.length;
    if (al === 0) return bl;
    if (bl === 0) return al;
    const prev = new Array(bl + 1);
    const curr = new Array(bl + 1);
    for (let j = 0; j <= bl; j++) prev[j] = j;
    for (let i = 1; i <= al; i++) {
        curr[0] = i;
        for (let j = 1; j <= bl; j++) {
            const cost = a.charCodeAt(i - 1) === b.charCodeAt(j - 1) ? 0 : 1;
            curr[j] = Math.min(
                curr[j - 1] + 1,
                prev[j] + 1,
                prev[j - 1] + cost,
            );
        }
        for (let j = 0; j <= bl; j++) prev[j] = curr[j];
    }
    return prev[bl];
};

// Allowed edit distance for a name of length `len`.
const allowedDistance = (len) => {
    if (len <= 3) return 1;
    if (len <= 6) return 2;
    return 3;
};

const normalizeName = (s) =>
    (s || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "") // strip diacritics
        .replace(/[^a-z0-9\s]/g, " ")
        .replace(/\s+/g, " ")
        .trim();

// Common short English fillers that Whisper emits on quiet audio. If the
// ENTIRE transcript is one of these we won't attempt a name match — this
// stops "you" / "so" / "well" / "one" from randomly triggering a fuzzy
// match against a student's name.
const NAME_MATCH_STOPWORDS = new Set([
    "you", "your", "yours", "so", "well", "one", "two", "three",
    "the", "a", "an", "and", "or", "but", "is", "it", "its",
    "this", "that", "these", "those", "here", "there",
    "he", "she", "him", "her", "his", "hers", "they", "them",
    "yes", "no", "yeah", "nope", "ok", "okay", "hi", "hello",
    "bye", "goodbye", "please", "sorry", "thanks",
    "in", "on", "at", "of", "to", "for", "with", "by",
    "very", "much", "quite", "just",
]);

// Search pool for voice name lookup. Uses the full studentGrades list so
// the teacher can jump BACK to a student whose row is currently hidden by
// the "Unfinished" filter (i.e. after their grade was saved, possibly with
// the wrong value that needs correcting). Subject and section filters are
// still respected so we don't cross-match to an unrelated class.
const voiceSearchPool = computed(() => {
    let pool = [...(props.studentGrades || [])];

    if (selectedSubjectFilter.value !== "all") {
        pool = pool.filter(
            (g) => g.subject_id === selectedSubjectFilter.value,
        );
    }

    if (selectedSectionFilter.value !== "all") {
        pool = pool.filter(
            (g) => g.section_id === selectedSectionFilter.value,
        );
    }

    return pool;
});

const findStudentByVoice = (transcript) => {
    const grades = voiceSearchPool.value;
    if (!grades || grades.length === 0) return null;

    const normalizedTranscript = normalizeName(transcript);
    // Require at least 3 characters — a 1-2 char transcript is almost
    // always a hallucination and can only fuzzy-match trivially.
    if (normalizedTranscript.length < 3) return null;
    const transcriptWords = normalizedTranscript.split(/\s+/).filter(Boolean);

    // If EVERY word in the transcript is a common English stopword, this is
    // Whisper hallucinating on silence — refuse to match anything.
    const anyNameLike = transcriptWords.some(
        (w) => w.length >= 3 && !NAME_MATCH_STOPWORDS.has(w),
    );
    if (!anyNameLike) {
        if (typeof console !== "undefined") {
            console.log(
                "[voice] name match refused (stopwords only):",
                normalizedTranscript,
            );
        }
        return null;
    }

    // Precompute normalized names + counts of each name across the visible
    // list so we can allow "first-name only" or "last-name only" when they're
    // unique among the students currently on screen. The visible list has one
    // row per subject, so dedupe by student id before counting.
    const firstCounts = new Map();
    const lastCounts = new Map();
    const students = [];
    const seenStudentIds = new Set();
    for (const grade of grades) {
        const first = normalizeName(grade.student?.first_name);
        const last = normalizeName(grade.student?.last_name);
        if (!first && !last) continue;
        const sid = grade.student?.id ?? `${first}|${last}`;
        if (!seenStudentIds.has(sid)) {
            seenStudentIds.add(sid);
            firstCounts.set(first, (firstCounts.get(first) || 0) + 1);
            lastCounts.set(last, (lastCounts.get(last) || 0) + 1);
        }
        students.push({ grade, first, last });
    }

    let bestMatch = null;
    let bestScore = 0;

    for (const { grade, first, last } of students) {
        const fullName = `${first} ${last}`.trim();
        const reverseName = `${last} ${first}`.trim();
        const fullNameNoSpace = `${first}${last}`;
        const reverseNameNoSpace = `${last}${first}`;

        let score = 0;

        if (
            fullName &&
            (normalizedTranscript.includes(fullName) ||
                normalizedTranscript === fullName)
        ) {
            score = 100;
        } else if (
            reverseName &&
            (normalizedTranscript.includes(reverseName) ||
                normalizedTranscript === reverseName)
        ) {
            score = 100;
        } else if (
            first &&
            last &&
            transcriptWords.includes(first) &&
            transcriptWords.includes(last)
        ) {
            score = 95;
        } else if (
            (fullNameNoSpace &&
                normalizedTranscript.includes(fullNameNoSpace)) ||
            (reverseNameNoSpace &&
                normalizedTranscript.includes(reverseNameNoSpace))
        ) {
            score = 90;
        } else {
            // Fuzzy full-name via Levenshtein against 2-word windows in the
            // transcript (handles "Ballagtas Raffael" -> "Balagtas Rafael").
            let fuzzyScore = 0;
            for (let i = 0; i < transcriptWords.length - 1; i++) {
                const pair = `${transcriptWords[i]} ${transcriptWords[i + 1]}`;
                const revPair = `${transcriptWords[i + 1]} ${transcriptWords[i]}`;
                const distFwd = levenshtein(pair, fullName);
                const distRev = levenshtein(revPair, fullName);
                const allowed = allowedDistance(fullName.length);
                const best = Math.min(distFwd, distRev);
                if (best <= allowed) {
                    fuzzyScore = Math.max(fuzzyScore, 88 - best * 2);
                }
            }

            // Single-name match: unique first/last name on screen, or fuzzy
            // match against any transcript word.
            let singleScore = 0;
            for (const word of transcriptWords) {
                if (word.length < 3) continue;

                // Exact single-name match (only valid if that name is
                // unique among visible students — otherwise we'd pick a
                // random namesake).
                if (word === first && firstCounts.get(first) === 1) {
                    singleScore = Math.max(singleScore, 82);
                }
                if (word === last && lastCounts.get(last) === 1) {
                    singleScore = Math.max(singleScore, 82);
                }

                // Prefix / suffix match on either name
                if (
                    first &&
                    first.length >= 4 &&
                    (first.startsWith(word) || word.startsWith(first))
                ) {
                    singleScore = Math.max(singleScore, 76);
                }
                if (
                    last &&
                    last.length >= 4 &&
                    (last.startsWith(word) || word.startsWith(last))
                ) {
                    singleScore = Math.max(singleScore, 76);
                }

                // Fuzzy single-name via Levenshtein
                if (first && first.length >= 3) {
                    const d = levenshtein(word, first);
                    if (d <= allowedDistance(first.length)) {
                        singleScore = Math.max(singleScore, 80 - d * 2);
                    }
                }
                if (last && last.length >= 3) {
                    const d = levenshtein(word, last);
                    if (d <= allowedDistance(last.length)) {
                        singleScore = Math.max(singleScore, 80 - d * 2);
                    }
                }
            }

            score = Math.max(fuzzyScore, singleScore);
        }

        if (score > bestScore) {
            bestScore = score;
            bestMatch = grade;
        }
    }

    // Acceptance threshold: 80 for real matches. Prevents Whisper's
    // hallucinated 1-word transcripts on silence from fuzzy-matching a
    // real student (that was scoring in the 72-79 range and randomly
    // selecting students when the mic was idle).
    if (bestScore >= 80) {
        return bestMatch;
    }

    // Silently drop unrecognized transcripts. Do not surface "Heard X" —
    // the whole point of strict mode is that non-name/non-grade audio is
    // completely ignored, so the UI should stay quiet.
    if (typeof console !== "undefined") {
        console.log("[voice] ignored non-name transcript:", transcript);
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

// Track how many times we've prompted so the wording feels natural on repeat.
let tablePromptCount = 0;
// One-time greeting per voice-mode session (resets when voice is turned off).
let voiceGreetingSpoken = false;

const timeOfDayGreeting = () => {
    const h = new Date().getHours();
    if (h < 12) return "Good morning";
    if (h < 18) return "Good afternoon";
    return "Good evening";
};

const buildTeacherGreeting = () => {
    const rawTitle = (props.user?.title || props.user?.gender || "").toString();
    let title = "Teacher";
    const t = rawTitle.toLowerCase();
    if (t === "male" || t === "m" || t.includes("sir") || t.includes("mr")) {
        title = "Sir";
    } else if (
        t === "female" ||
        t === "f" ||
        t.includes("ma'am") ||
        t.includes("ms") ||
        t.includes("mrs")
    ) {
        title = "Ma'am";
    }
    const last = (props.user?.last_name || "").toString().trim();
    const first = (props.user?.first_name || "").toString().trim();
    const name = last || first;
    const address = name ? `${title} ${name}` : "teacher";
    return `${timeOfDayGreeting()}, ${address}! Voice grade entry is on. Say a student name, then say the grade.`;
};

const startTableVoiceRecognition = async (options = {}) => {
    currentVoiceMode = "table";
    const quiet = Boolean(options.quiet);

    if (!quiet) {
        let prompt;
        if (!voiceGreetingSpoken) {
            voiceGreetingSpoken = true;
            prompt = `${buildTeacherGreeting()} What is the student's name?`;
        } else if (focusedGradeRow.value) {
            const name =
                studentSpokenName(focusedGradeRow.value.student) ||
                "this student";
            prompt = `${name}. Say the grade.`;
        } else if (tablePromptCount === 0) {
            prompt = "What is the student's name?";
        } else {
            prompt = "Next student, please.";
        }
        tablePromptCount++;
        tableVoiceStatus.value = prompt;
        await speakPrompt(prompt);
    }

    const ok = await startGroqStream();
    if (!ok) {
        tableVoiceStatus.value = "Failed to start voice. Try again.";
        return;
    }
    tableVoiceActive.value = true;
    tableVoiceStatus.value = focusedGradeRow.value
        ? `The system is listening — say the Term ${selectedTermNumber.value} grade`
        : "The system is listening — say a student name";
};

const stopTableVoiceRecognition = () => {
    cancelSpeech();
    stopGroqStream();
    tableVoiceActive.value = false;
    tablePromptCount = 0;
};

const openStudentModal = (student) => {
    selectedStudent.value = student;
    showStudentModal.value = true;
};

const closeStudentModal = () => {
    showStudentModal.value = false;
    selectedStudent.value = null;
};

const formatDate = (date) => {
    if (!date) return null;
    const d = new Date(date);
    return d.toLocaleDateString("en-US", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

// Find the next grade row in the table that still has an empty term. When
// the teacher is doing bulk voice entry we want to jump straight into the
// next student's modal instead of dumping them back on the student picker.
// Preference order:
//   1. Same subject, appearing AFTER the current row (natural top-to-bottom).
//   2. Same subject, appearing BEFORE the current row (wraparound).
//   3. Any subject, incomplete (last resort, in case the current subject
//      is finished).
const findNextIncompleteGrade = (currentGrade) => {
    const rows = filteredGrades.value || [];
    if (rows.length === 0) return null;
    const currentSubjectId = currentGrade?.subject_id ?? null;
    const currentStudentId = currentGrade?.student?.id ?? null;
    const isIncomplete = (g) => {
        if (selectedTermNumber.value === 1) return !g.term_1;
        if (selectedTermNumber.value === 2) return !g.term_2;
        if (selectedTermNumber.value === 3) return !g.term_3;
        return !g.term_1 || !g.term_2 || !g.term_3 || !g.final_grade;
    };

    const currentIndex = rows.findIndex(
        (g) =>
            g.student?.id === currentStudentId &&
            g.subject_id === currentSubjectId,
    );

    const sameSubject = (g) =>
        currentSubjectId == null || g.subject_id === currentSubjectId;

    if (currentIndex >= 0) {
        for (let i = currentIndex + 1; i < rows.length; i++) {
            const g = rows[i];
            if (sameSubject(g) && isIncomplete(g)) return g;
        }
        for (let i = 0; i < currentIndex; i++) {
            const g = rows[i];
            if (sameSubject(g) && isIncomplete(g)) return g;
        }
    }

    for (const g of rows) {
        if (g.student?.id === currentStudentId && g.subject_id === currentSubjectId) {
            continue;
        }
        if (isIncomplete(g)) return g;
    }
    return null;
};

// Smooth transition from the current grade modal into another one without
// bouncing through the table voice picker in between.
const switchToGradeModal = (nextGrade) => {
    if (!nextGrade) return;
    // Keep the mic hot across students so the next "Term one" prompt is
    // already listening when it finishes speaking.
    cancelSpeech();
    clearPendingTens();
    isVoiceActive.value = false;
    voiceTranscript.value = "";
    showGradeModal.value = false;
    selectedGrade.value = null;
    gradeForm.value = { term_1: null, term_2: null, term_3: null };
    currentVoiceMode = "modal";
    groqGeneration++;
    setTimeout(() => {
        openGradeModal(nextGrade);
    }, 80);
};

const submitGrades = () => {
    // Safety guard: refuse to save when nothing has been entered yet.
    // A false "save" trigger from a Whisper hallucination on an empty form
    // would otherwise submit all-null grades and cause the auto-advance to
    // jump to another student. This makes the voice pipeline robust to
    // stray words like "so" / "sim" / "sam" that used to trip save.
    const anyTermFilled =
        gradeForm.value.term_1 != null ||
        gradeForm.value.term_2 != null ||
        gradeForm.value.term_3 != null;
    if (!anyTermFilled) {
        if (typeof console !== "undefined") {
            console.log("[voice] save ignored — no grades entered yet");
        }
        voiceStatus.value = "Enter a grade first, then say save";
        // Speak it too so the teacher doesn't have to look at the screen
        // to figure out why nothing happened.
        speakPrompt("Enter a grade first.", { wait: false });
        return;
    }

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
                toast.success(
                    "Grades saved. SF9 and SP-10 will use this one-time entry.",
                );
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
                    <div class="label">Noted by: ${props.schoolHead || "School Head"}</div>
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
                    <div class="label">Noted by: ${props.schoolHead || "School Head"}</div>
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
    scrollbar-width: thin;
    scrollbar-color: #c9a227 #002244;
}

.sidebar-nav::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: #002244;
    border-radius: 8px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #c9a227;
    border-radius: 8px;
    border: 2px solid #002244;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #e0b93a;
}

.sidebar-nav::-webkit-scrollbar-button {
    display: none;
    width: 0;
    height: 0;
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

.header-right {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.header-logout-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.42rem 0.85rem;
    border: none;
    border-radius: 6px;
    background: #003366;
    color: #fff;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.82rem;
    font-family: inherit;
    white-space: nowrap;
}

.header-logout-btn:hover {
    background: #c9a227;
    color: #003366;
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
    background: white;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #003366;
    padding: 0.65rem 0.9rem;
    border-radius: 0;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.table-voice-bar.listening-banner {
    background: #003366;
    color: #fff;
    border: 1px solid #00264d;
    border-left: 4px solid #c9a227;
}

.listening-main {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.listening-copy {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.listening-copy strong {
    font-size: 0.95rem;
    letter-spacing: 0.02em;
}

.table-voice-bar.listening-banner .table-voice-text {
    color: rgba(255, 255, 255, 0.88);
    font-weight: 500;
    font-size: 0.82rem;
}

.table-voice-bar.listening-banner .table-voice-transcript {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.2);
    color: #fff;
}

.table-voice-bar.listening-banner .command-tag {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.25);
    color: #fff;
    border-radius: 0;
}

.table-voice-status {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.table-voice-status .voice-indicator,
.voice-indicator.listening {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    gap: 0.4rem;
    width: 42px;
    height: 42px;
    background: #9b1c1c;
    flex-shrink: 0;
}

.voice-indicator.listening .pulse-rings {
    position: absolute;
    inset: -6px;
    border: 2px solid rgba(201, 162, 39, 0.7);
    animation: listen-ring 1.4s ease-out infinite;
}

.table-voice-status .pulse-dot,
.voice-indicator.listening .pulse-dot {
    width: 8px;
    height: 8px;
    background: #c9a227;
    border-radius: 50%;
    animation: pulse-dot 1.1s infinite;
}

.table-voice-status .mic-icon-active,
.voice-indicator.listening .mic-icon-active {
    color: #fff;
}

@keyframes listen-ring {
    0% {
        transform: scale(0.85);
        opacity: 0.9;
    }
    100% {
        transform: scale(1.25);
        opacity: 0;
    }
}

.table-voice-text {
    font-weight: 600;
    font-size: 0.85rem;
    color: #003366;
}

.table-voice-transcript {
    font-style: italic;
    color: #475569;
    font-size: 0.8rem;
    padding: 0.25rem 0.6rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    word-break: break-word;
}

.table-voice-commands {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.command-tag {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.2px;
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

.data-table th.active-term-col {
    background: #003366;
    color: #fff;
    border-bottom-color: #c9a227;
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

.action-btn.edit:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #fff;
    color: #003366;
}

.action-btn.form-link {
    text-decoration: none;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.2rem 0.35rem;
}

.grade-once-note,
.grade-once-inline {
    margin: 0 0 0.85rem;
    color: #444;
    font-size: 0.86rem;
}

.grade-once-inline {
    margin: 0.85rem 0 0;
}

.grade-once-inline a,
.grade-once-note a {
    color: #003366;
    font-weight: 700;
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
    padding: 0.45rem 0.85rem;
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
    gap: 0.7rem;
    z-index: 1;
}

.subjects-icon {
    width: 28px;
    height: 28px;
    background: #00264d;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.subjects-details h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: white;
    line-height: 1.2;
}

.subjects-count-text {
    display: block;
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.85);
    margin-top: 0.1rem;
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
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
    border: 1px solid #c5c5c5;
    transition: border-color 0.2s ease;
}

.subject-card:hover {
    transform: none;
    border-color: #003366;
    box-shadow: none;
}

.subject-card-header {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.55rem 0.75rem;
    background: #003366;
    border-bottom: 3px solid #c9a227;
}

.subject-icon-wrapper {
    width: 32px;
    height: 32px;
    background: #00264d;
    border-radius: 0;
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

.subject-card-header .subject-name {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #fff;
    line-height: 1.25;
}

.subject-card-header .subject-code {
    display: inline-block;
    margin-top: 0.15rem;
    font-size: 0.72rem;
    color: #fff;
    font-weight: 500;
    background: rgba(255, 255, 255, 0.16);
    padding: 0.1rem 0.4rem;
    border-radius: 0;
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
    background: #003366;
    color: #fff;
    border-radius: 0;
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
    background: #003366;
    color: white;
    border: none;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s ease;
    width: 100%;
    justify-content: center;
    text-decoration: none;
}

.view-students-btn:hover {
    transform: none;
    background: #00264d;
    box-shadow: none;
}

/* Subject Students Modal */
.subject-students-modal .modal-header.gradient-header {
    background: #003366;
    padding: 0.55rem 0.85rem;
    border-bottom: 3px solid #c9a227;
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

.filter-select.term-required {
    border-color: #c9a227;
    background: #fffdf4;
}

.term-lock-note {
    margin: 0 0 0.75rem;
    padding: 0.45rem 0.7rem;
    background: #fff8e1;
    border: 1px solid #e6d08a;
    color: #6b5200;
    font-size: 0.84rem;
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
    border-radius: 0;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    border: 1px solid #c5c5c5;
}

.modal-container.large {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.55rem 0.85rem;
    border-bottom: 3px solid #c9a227;
    background: #003366;
    color: white;
    border-radius: 0;
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
    background: none;
    border: none;
    padding: 0.2rem;
    border-radius: 0;
    cursor: pointer;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
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

/* ============================================================
   Grade Entry Modal — clean design matching Enrollment Details
   ============================================================ */

.modal-container.grade-modal-clean {
    max-width: 720px;
    width: 95%;
    border-radius: 0;
    background: white;
    border: 1px solid #c5c5c5;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 92vh;
}

.grade-clean-header {
    padding: 0.6rem 0.95rem;
    background: #003366;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 3px solid #c9a227;
}

.grade-clean-header h3 {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    letter-spacing: 0.2px;
}

.grade-clean-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.2rem;
    border-radius: 0;
    transition: color 0.15s ease;
}

.grade-clean-close:hover {
    color: #c9a227;
    background: none;
}

.grade-clean-body {
    padding: 1rem 1.15rem;
    overflow-y: auto;
    background: white;
    flex: 1;
}

/* Student header block (avatar + name + LRN) */
.grade-detail-header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #d8d8d8;
    margin-bottom: 1rem;
}

.grade-detail-avatar {
    width: 48px;
    height: 48px;
    background: #003366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    font-weight: 700;
    overflow: hidden;
    flex-shrink: 0;
    border-radius: 0;
}

.grade-detail-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.grade-detail-title h4 {
    margin: 0 0 0.35rem 0;
    font-size: 1.02rem;
    color: #003366;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}

.grade-lrn-badge {
    display: inline-block;
    padding: 0.3rem 0.55rem;
    background: #fff;
    color: #333;
    border: 1px solid #ccc;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: monospace;
    border-radius: 0;
}

/* Voice mode compact panel */
.grade-voice-panel {
    border: 1px solid #c5c5c5;
    border-top: 3px solid #c9a227;
    background: #fdfaf0;
    padding: 0.6rem 0.75rem;
    margin-bottom: 1rem;
}

.grade-voice-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.grade-voice-label {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 700;
    color: #003366;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.grade-voice-status {
    flex: 1;
    font-size: 0.82rem;
    color: #333;
    font-style: italic;
}

.grade-voice-off {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.55rem;
    background: white;
    border: 1px solid #9b1c1c;
    color: #9b1c1c;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    cursor: pointer;
    border-radius: 0;
    transition: all 0.15s ease;
}

.grade-voice-off:hover {
    background: #9b1c1c;
    color: white;
}

.grade-voice-transcript {
    margin-top: 0.5rem;
    padding: 0.35rem 0.55rem;
    background: white;
    border: 1px solid #e2d9b5;
    font-size: 0.82rem;
    font-style: italic;
    color: #555;
    word-break: break-word;
}

.grade-voice-hint {
    margin-top: 0.5rem;
    font-size: 0.72rem;
    color: #64748b;
    letter-spacing: 0.2px;
}

.grade-voice-toggle-row {
    margin-bottom: 1rem;
}

.grade-voice-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    background: white;
    border: 1px solid #003366;
    color: #003366;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    cursor: pointer;
    border-radius: 0;
    transition: all 0.15s ease;
}

.grade-voice-toggle-btn:hover {
    background: #003366;
    color: white;
}

/* 2-column detail grid */
.grade-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem 1rem;
}

.grade-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    padding: 0.35rem 0.5rem;
    border: 1px solid transparent;
    transition: border-color 0.15s ease, background 0.15s ease;
}

.grade-detail-item.full-width {
    grid-column: 1 / -1;
}

.grade-detail-item.active-term {
    border-color: #c9a227;
    background: #fdfaf0;
}

.grade-detail-item.locked-term {
    opacity: 0.55;
}

.grade-term-badge {
    display: inline-block;
    margin-top: 0.25rem;
    background: #003366;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.12rem 0.4rem;
}

.grade-detail-item label {
    font-size: 0.7rem;
    color: #555;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.grade-detail-item > span {
    font-size: 0.92rem;
    color: #222;
}

.grade-detail-item .grade-muted {
    color: #94a3b8;
    font-style: italic;
    font-size: 0.82rem;
}

/* Numeric term input styled like the Enrollment "80.00" outlined box */
.grade-term-input {
    width: 100%;
    max-width: 160px;
    padding: 0.4rem 0.6rem;
    border: 1px solid #c9a227;
    background: white;
    font-size: 1rem;
    font-weight: 700;
    color: #003366;
    font-family: monospace;
    text-align: left;
    border-radius: 0;
    outline: none;
    appearance: textfield;
}

.grade-term-input::-webkit-outer-spin-button,
.grade-term-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.grade-term-input:focus {
    border-color: #003366;
    box-shadow: inset 0 0 0 1px #003366;
}

.grade-term-input::placeholder {
    color: #cbd5e1;
    font-weight: 400;
}

.grade-term-status {
    display: inline-block;
    margin-top: 0.15rem;
    padding: 0.15rem 0.4rem;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    border: 1px solid #c5c5c5;
    color: #003366;
    background: white;
    width: fit-content;
    text-transform: uppercase;
}

.grade-term-status.excellent {
    border-color: #003366;
    color: #003366;
}
.grade-term-status.very-good {
    border-color: #003366;
    color: #003366;
}
.grade-term-status.good {
    border-color: #9a6700;
    color: #9a6700;
}
.grade-term-status.satisfactory {
    border-color: #9a6700;
    color: #9a6700;
}
.grade-term-status.needs-improvement {
    border-color: #9b1c1c;
    color: #9b1c1c;
}

/* Final Grade value box (mirrors the GWA box in Enrollment Details) */
.grade-value-box {
    display: inline-block;
    padding: 0.35rem 0.6rem;
    border: 1px solid #c9a227;
    background: white;
    color: #003366;
    font-weight: 700;
    font-size: 0.95rem;
    font-family: monospace;
    width: fit-content;
    border-radius: 0;
}

.grade-value-box.needs-improvement {
    color: #9b1c1c;
    border-color: #9b1c1c;
}

.grade-value-box.excellent,
.grade-value-box.very-good {
    color: #003366;
    border-color: #003366;
}

/* Footer */
.grade-clean-footer {
    padding: 0.7rem 1.15rem;
    background: white;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.grade-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    background: white;
    color: #333;
    border: 1px solid #bdbdbd;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    border-radius: 0;
    transition: background 0.15s ease;
}

.grade-btn-secondary:hover {
    background: #f4f4f4;
}

.grade-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 0.9rem;
    background: #003366;
    color: white;
    border: 1px solid #003366;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    border-radius: 0;
    transition: background 0.15s ease;
}

.grade-btn-primary:hover:not(:disabled) {
    background: #00264d;
}

.grade-btn-primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.grade-btn-primary .spin {
    animation: spin 1s linear infinite;
}

@media (max-width: 640px) {
    .grade-detail-grid {
        grid-template-columns: 1fr;
    }
    .grade-detail-item.full-width {
        grid-column: 1 / -1;
    }
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

.student-grade-info .subject-name {
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 0.25rem;
}

/* Voice Control Styles */
.voice-control-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0;
    padding: 0.6rem 0.85rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #003366;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.voice-control-section.voice-off {
    background: white;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #e2e8f0;
    box-shadow: none;
}

.voice-mode-indicator {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.mic-icon-active {
    color: #003366;
}

.voice-mode-label {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.85rem;
    letter-spacing: 0.2px;
}

.voice-disable-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.65rem;
    background: white;
    border: 1px solid #cbd5e1;
    color: #475569;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.voice-disable-btn:hover {
    background: #003366;
    border-color: #003366;
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

.voice-mode-toggle:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.voice-mode-toggle.active,
.voice-mode-toggle.listening {
    background: #9b1c1c;
    border-color: #9b1c1c;
    color: white;
}

.voice-mode-toggle.active .shortcut-key,
.voice-mode-toggle.listening .shortcut-key {
    background: #fff;
    border-color: #fff;
    color: #9b1c1c;
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
    padding: 0.55rem 1rem;
    border: 1px solid #003366;
    background: white;
    color: #003366;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.voice-toggle-btn:hover {
    background: #003366;
    color: white;
}

.voice-toggle-btn.active {
    background: #003366;
    border-color: #003366;
    color: white;
}

.voice-status-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.voice-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.pulse-dot {
    width: 8px;
    height: 8px;
    background: #003366;
    border-radius: 50%;
    animation: pulse-dot 1.6s infinite;
}

@keyframes pulse-dot {
    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.35);
        opacity: 0.55;
    }
}

.voice-label {
    font-size: 0.8rem;
    color: #475569;
    font-weight: 500;
}

.current-quarter-badge {
    background: #003366;
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.75rem;
    letter-spacing: 0.4px;
}

.voice-status-bar {
    background: white;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #003366;
    padding: 0.65rem 0.85rem;
    border-radius: 10px;
    margin-bottom: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.voice-status-text {
    font-size: 0.85rem;
    font-weight: 600;
    color: #003366;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.voice-transcript {
    font-style: italic;
    color: #475569;
    margin-top: 0.3rem;
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    word-break: break-word;
}

.voice-commands {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed #e2e8f0;
}

.command-hint {
    font-size: 0.7rem;
    color: #64748b;
    letter-spacing: 0.2px;
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
    background: #003366;
    padding: 0.55rem 0.85rem;
    border-bottom: 3px solid #c9a227;
}

.gradient-header .header-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.gradient-header .header-icon {
    background: transparent;
    padding: 0;
    border-radius: 0;
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
    background: #f4f4f4;
    border-radius: 0;
    margin-bottom: 1.75rem;
    border: 1px solid #c5c5c5;
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

    .user-name,
    .user-role {
        display: none;
    }

    .header-logout-btn .logout-text {
        display: none;
    }

    .header-logout-btn {
        padding: 0.42rem 0.55rem;
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
        gap: 0.55rem;
        padding: 0.55rem 0.75rem;
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
