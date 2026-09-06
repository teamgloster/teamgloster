<template>
    <AdminLayout
        title="Students"
        pageTitle="Students"
        currentPage="students"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Students</h2>
                    <span class="gov-sy"
                        >School Year {{ currentSchoolYear }}</span
                    >
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Eligible to Promote</div>
                    <div class="gov-stat-value">{{ eligibleCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Incomplete Grades</div>
                    <div class="gov-stat-value">{{ incompleteCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Failed Previous Level</div>
                    <div class="gov-stat-value">{{ failedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Already Promoted</div>
                    <div class="gov-stat-value">{{ promotedCount }}</div>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="studentSearch"
                            type="text"
                            placeholder="Search students by name, LRN, or email..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select v-model="yearLevelFilter" class="filter-select">
                            <option value="all">All Year Levels</option>
                            <option
                                v-for="level in yearLevels"
                                :key="level.id"
                                :value="level.id"
                            >
                                {{ level.name }}
                            </option>
                        </select>
                        <select v-model="sectionFilter" class="filter-select">
                            <option value="all">All Sections</option>
                            <option
                                v-for="section in availableFilterSections"
                                :key="section.id"
                                :value="section.id"
                            >
                                <template v-if="yearLevelFilter === 'all'">
                                    {{ section.year_level?.name }} —
                                    {{ section.name }}
                                </template>
                                <template v-else>
                                    {{ section.name }}
                                </template>
                                <template v-if="section.school_year">
                                    ({{ section.school_year }})
                                </template>
                            </option>
                        </select>
                        <select v-model="genderFilter" class="filter-select">
                            <option value="all">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <select
                            v-model="enrollmentStatusFilter"
                            class="filter-select"
                        >
                            <option value="all">All Enrollment Status</option>
                            <option value="none">No Enrollment</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <select
                            v-model="promotionFilter"
                            class="filter-select"
                        >
                            <option value="all">All Promotion Status</option>
                            <option value="eligible">Eligible to Promote</option>
                            <option value="incomplete">Incomplete Grades</option>
                            <option value="failed">Failed</option>
                            <option value="already_promoted">
                                Already Promoted
                            </option>
                            <option value="completed">Completed</option>
                            <option value="no_record">No Record</option>
                        </select>
                        <button
                            type="button"
                            class="btn-success"
                            :disabled="filteredEligibleStudents.length === 0"
                            @click="openBulkPromoteModal"
                        >
                            <TrendingUp :size="18" />
                            Promote Eligible
                        </button>
                        <button
                            class="btn-primary"
                            @click="openStudentModal('add')"
                        >
                            <UserPlus :size="18" />
                            Add Student
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Year Level</th>
                            <th>Promotion</th>
                            <th>Section</th>
                            <th>LRN</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Gender</th>
                            <th>Guardian</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="student in filteredStudents"
                            :key="student.id"
                        >
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm">
                                        {{ getInitials(student) }}
                                    </div>
                                    <div class="user-info-cell">
                                        <span class="user-name-cell">
                                            {{ student.last_name }},
                                            {{ student.first_name }}
                                            {{
                                                student.middle_name
                                                    ? student.middle_name.charAt(
                                                          0,
                                                      ) + "."
                                                    : ""
                                            }}
                                            {{ student.suffix || "" }}
                                        </span>
                                        <span class="user-meta">
                                            {{
                                                formatDateFull(
                                                    student.date_of_birth,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    v-if="studentYearLevelName(student)"
                                    class="year-level-badge"
                                >
                                    {{ studentYearLevelName(student) }}
                                </span>
                                <span v-else class="text-muted">—</span>
                            </td>
                            <td>
                                <div class="promotion-cell">
                                    <span
                                        class="status-badge"
                                        :class="
                                            student.promotion?.status ||
                                            'no_record'
                                        "
                                    >
                                        {{
                                            promotionLabel(
                                                student.promotion?.status,
                                            )
                                        }}
                                    </span>
                                    <small
                                        v-if="student.promotion?.gwa != null"
                                    >
                                        GWA
                                        {{
                                            Number(
                                                student.promotion.gwa,
                                            ).toFixed(2)
                                        }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span
                                    v-if="studentSectionName(student)"
                                    class="section-badge"
                                >
                                    {{ studentSectionName(student) }}
                                </span>
                                <span v-else class="text-muted">
                                    Not assigned
                                </span>
                            </td>
                            <td>
                                <span class="lrn-badge">{{ student.lrn }}</span>
                            </td>
                            <td>{{ student.email }}</td>
                            <td>{{ student.phone_no || "-" }}</td>
                            <td>
                                <span
                                    class="gender-badge"
                                    :class="student.gender"
                                >
                                    {{
                                        student.gender
                                            ? student.gender
                                                  .charAt(0)
                                                  .toUpperCase() +
                                              student.gender.slice(1)
                                            : "-"
                                    }}
                                </span>
                            </td>
                            <td>
                                <div
                                    v-if="student.guardian_full_name"
                                    class="guardian-info"
                                >
                                    <span>{{
                                        student.guardian_full_name
                                    }}</span>
                                    <small>{{
                                        student.guardian_contact_no
                                    }}</small>
                                </div>
                                <span v-else>-</span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewStudent(student)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="
                                            openStudentModal('edit', student)
                                        "
                                        title="Edit Student"
                                    >
                                        <Pencil :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon promote"
                                        :disabled="
                                            !student.promotion?.can_promote
                                        "
                                        @click="openPromoteModal(student)"
                                        :title="
                                            student.promotion?.can_promote
                                                ? 'Promote to next year level'
                                                : student.promotion?.message ||
                                                  'Student has not passed the previous year level'
                                        "
                                    >
                                        <TrendingUp :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon edit"
                                        @click="openYearLevelModal(student)"
                                        title="Change year level"
                                    >
                                        <ArrowUpDown :size="16" />
                                    </button>
                                    <button
                                        class="btn-icon delete"
                                        @click="confirmDeleteStudent(student)"
                                        title="Delete Student"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="10" class="empty-table">
                                <div class="empty-message">
                                    <Users :size="40" />
                                    <p>No students found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ filteredStudents.length }} of
                    {{ students.length }} students
                </span>
            </div>
        </div>

        <!-- Student Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showStudentModal"
                    class="modal-overlay"
                    @click.self="closeStudentModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header">
                            <h3>
                                <UserPlus
                                    v-if="studentModalMode === 'add'"
                                    :size="22"
                                />
                                <Pencil
                                    v-else-if="studentModalMode === 'edit'"
                                    :size="22"
                                />
                                <Eye v-else :size="22" />
                                {{
                                    studentModalMode === "add"
                                        ? "Add New Student"
                                        : studentModalMode === "edit"
                                          ? "Edit Student"
                                          : "Student Details"
                                }}
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeStudentModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <!-- View Mode -->
                            <div
                                v-if="studentModalMode === 'view'"
                                class="view-details"
                            >
                                <div class="detail-header">
                                    <div class="detail-avatar">
                                        {{ getInitials(selectedStudent) }}
                                    </div>
                                    <div class="detail-title">
                                        <h4>
                                            {{ selectedStudent.first_name }}
                                            {{ selectedStudent.middle_name }}
                                            {{ selectedStudent.last_name }}
                                            {{ selectedStudent.suffix }}
                                        </h4>
                                        <span class="lrn-badge large"
                                            >LRN:
                                            {{ selectedStudent.lrn }}</span
                                        >
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>Email</label>
                                        <span>{{ selectedStudent.email }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Phone</label>
                                        <span>{{
                                            selectedStudent.phone_no ||
                                            "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Date of Birth</label>
                                        <span>{{
                                            formatDateFull(
                                                selectedStudent.date_of_birth,
                                            ) || "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Gender</label>
                                        <span>{{
                                            selectedStudent.gender
                                                ? selectedStudent.gender
                                                      .charAt(0)
                                                      .toUpperCase() +
                                                  selectedStudent.gender.slice(
                                                      1,
                                                  )
                                                : "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Guardian Name</label>
                                        <span>{{
                                            selectedStudent.guardian_full_name ||
                                            "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Guardian Contact</label>
                                        <span>{{
                                            selectedStudent.guardian_contact_no ||
                                            "Not provided"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Current Year Level</label>
                                        <span>{{
                                            selectedStudent.current_enrollment
                                                ?.year_level?.name ||
                                            selectedStudent.promotion
                                                ?.current_year_level?.name ||
                                            "Not enrolled"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Promotion Status</label>
                                        <span>{{
                                            selectedStudent.promotion
                                                ?.message ||
                                            "No promotion record"
                                        }}</span>
                                    </div>
                                </div>
                                <div class="modal-footer-inline">
                                    <button
                                        type="button"
                                        class="btn-secondary"
                                        @click="
                                            openYearLevelModal(selectedStudent)
                                        "
                                    >
                                        <ArrowUpDown :size="16" />
                                        Change Year Level
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-success"
                                        :disabled="
                                            !selectedStudent.promotion
                                                ?.can_promote
                                        "
                                        @click="
                                            openPromoteModal(selectedStudent)
                                        "
                                    >
                                        <TrendingUp :size="16" />
                                        Promote
                                    </button>
                                </div>
                            </div>

                            <!-- Add/Edit Form -->
                            <form
                                v-else
                                @submit.prevent="submitStudentForm"
                                class="modal-form"
                            >
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >First Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="studentForm.first_name"
                                            type="text"
                                            required
                                            placeholder="Enter first name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input
                                            v-model="studentForm.middle_name"
                                            type="text"
                                            placeholder="Enter middle name"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >Last Name
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="studentForm.last_name"
                                            type="text"
                                            required
                                            placeholder="Enter last name"
                                        />
                                    </div>
                                    <div class="form-group small">
                                        <label>Suffix</label>
                                        <input
                                            v-model="studentForm.suffix"
                                            type="text"
                                            placeholder="Jr., III"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label
                                            >LRN
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="studentForm.lrn"
                                            type="text"
                                            required
                                            maxlength="12"
                                            placeholder="12-digit LRN"
                                            @input="formatLRN"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label
                                            >Email
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="studentForm.email"
                                            type="email"
                                            required
                                            placeholder="student@email.com"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input
                                            v-model="studentForm.phone_no"
                                            type="tel"
                                            placeholder="09XXXXXXXXX"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Date of Birth</label>
                                        <input
                                            v-model="studentForm.date_of_birth"
                                            type="date"
                                        />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select v-model="studentForm.gender">
                                            <option value="">
                                                Select gender
                                            </option>
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        class="form-group"
                                        v-if="studentModalMode === 'add'"
                                    >
                                        <label
                                            >Password
                                            <span class="required"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="studentForm.password"
                                            type="password"
                                            :required="
                                                studentModalMode === 'add'
                                            "
                                            placeholder="Minimum 8 characters"
                                        />
                                    </div>
                                </div>
                                <div class="form-divider">
                                    <span>Guardian Information</span>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Guardian Full Name</label>
                                        <input
                                            v-model="
                                                studentForm.guardian_full_name
                                            "
                                            type="text"
                                            placeholder="Enter guardian's full name"
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Guardian Contact</label>
                                        <input
                                            v-model="
                                                studentForm.guardian_contact_no
                                            "
                                            type="tel"
                                            placeholder="09XXXXXXXXX"
                                        />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeStudentModal"
                            >
                                {{
                                    studentModalMode === "view"
                                        ? "Close"
                                        : "Cancel"
                                }}
                            </button>
                            <button
                                v-if="studentModalMode !== 'view'"
                                type="submit"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="submitStudentForm"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                {{
                                    studentModalMode === "add"
                                        ? "Create Student"
                                        : "Save Changes"
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showDeleteModal"
                    class="modal-overlay"
                    @click.self="showDeleteModal = false"
                >
                    <div class="modal-container small">
                        <div class="modal-header danger">
                            <h3>
                                <AlertTriangle :size="22" />
                                Confirm Delete
                            </h3>
                            <button
                                class="close-btn"
                                @click="showDeleteModal = false"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="delete-warning">
                                <div class="warning-icon">
                                    <Trash2 :size="40" />
                                </div>
                                <p>
                                    Are you sure you want to delete this
                                    student?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{ studentToDelete?.first_name }}
                                        {{ studentToDelete?.last_name }}</strong
                                    >
                                    <span>LRN: {{ studentToDelete?.lrn }}</span>
                                </div>
                                <p class="warning-text">
                                    This action cannot be undone. All related
                                    data will be permanently deleted.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="showDeleteModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="isSubmitting"
                                @click="deleteStudent"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Delete Student
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Promote Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showPromoteModal"
                    class="modal-overlay"
                    @click.self="closePromoteModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header">
                            <h3>
                                <TrendingUp :size="22" />
                                Promote Student
                            </h3>
                            <button class="close-btn" @click="closePromoteModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <p>
                                    Promote
                                    <strong>
                                        {{ studentToPromote?.last_name }},
                                        {{ studentToPromote?.first_name }}
                                    </strong>
                                    from
                                    {{
                                        studentToPromote?.promotion
                                            ?.source_year_level?.name ||
                                        "their current year level"
                                    }}
                                    to
                                    <strong>
                                        {{
                                            studentToPromote?.promotion
                                                ?.next_year_level?.name
                                        }}
                                    </strong>
                                    for School Year {{ currentSchoolYear }}?
                                </p>
                                <p
                                    v-if="studentToPromote?.promotion?.message"
                                    class="description-text"
                                >
                                    {{ studentToPromote.promotion.message }}
                                </p>
                                <div class="form-group">
                                    <label>Remarks (optional)</label>
                                    <textarea
                                        v-model="promotionRemarks"
                                        rows="3"
                                        placeholder="Add a note for this promotion"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closePromoteModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="promoteStudent"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Promote Student
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Change Year Level Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showYearLevelModal"
                    class="modal-overlay"
                    @click.self="closeYearLevelModal"
                >
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3>
                                <ArrowUpDown :size="22" />
                                Change Year Level
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeYearLevelModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="description-text">
                                {{
                                    studentToChange?.promotion?.message ||
                                    "Select a year level for this student."
                                }}
                            </p>
                            <div class="modal-form">
                                <div class="form-group">
                                    <label>
                                        Year Level
                                        <span class="required">*</span>
                                    </label>
                                    <select v-model="yearLevelForm.year_level_id">
                                        <option value="">
                                            Select year level
                                        </option>
                                        <option
                                            v-for="level in changeableYearLevels"
                                            :key="level.id"
                                            :value="String(level.id)"
                                            :disabled="
                                                !isYearLevelAllowed(level.id)
                                            "
                                        >
                                            {{ level.name }}
                                            <template
                                                v-if="
                                                    !isYearLevelAllowed(
                                                        level.id,
                                                    )
                                                "
                                            >
                                                — not yet passed
                                            </template>
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Remarks (optional)</label>
                                    <textarea
                                        v-model="yearLevelForm.remarks"
                                        rows="3"
                                        placeholder="Reason for changing the year level"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeYearLevelModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="
                                    isSubmitting ||
                                    !yearLevelForm.year_level_id ||
                                    isSameCurrentYearLevel
                                "
                                @click="submitYearLevelChange"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Save Year Level
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Bulk Promote Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showBulkPromoteModal"
                    class="modal-overlay"
                    @click.self="showBulkPromoteModal = false"
                >
                    <div class="modal-container small">
                        <div class="modal-header">
                            <h3>
                                <TrendingUp :size="22" />
                                Promote Eligible Students
                            </h3>
                            <button
                                class="close-btn"
                                @click="showBulkPromoteModal = false"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <p>
                                    Promote
                                    <strong>{{
                                        filteredEligibleStudents.length
                                    }}</strong>
                                    student(s) who have passed their current
                                    year level to the next grade for School Year
                                    {{ currentSchoolYear }}?
                                </p>
                                <p class="description-text">
                                    Students who failed or still have incomplete
                                    final grades will not be promoted.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="showBulkPromoteModal = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="promoteEligibleStudents"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Promote Eligible
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {
    Users,
    Search,
    UserPlus,
    Eye,
    Pencil,
    Trash2,
    X,
    Loader2,
    AlertTriangle,
    TrendingUp,
    ArrowUpDown,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    students: {
        type: Array,
        default: () => [],
    },
    yearLevels: {
        type: Array,
        default: () => [],
    },
    sections: {
        type: Array,
        default: () => [],
    },
    currentSchoolYear: {
        type: String,
        default: "",
    },
});

// Student Management State
const studentSearch = ref("");
const yearLevelFilter = ref("all");
const sectionFilter = ref("all");
const genderFilter = ref("all");
const enrollmentStatusFilter = ref("all");
const promotionFilter = ref("all");
const showStudentModal = ref(false);
const studentModalMode = ref("add");
const selectedStudent = ref(null);
const showDeleteModal = ref(false);
const studentToDelete = ref(null);
const showPromoteModal = ref(false);
const studentToPromote = ref(null);
const promotionRemarks = ref("");
const showYearLevelModal = ref(false);
const studentToChange = ref(null);
const showBulkPromoteModal = ref(false);
const isSubmitting = ref(false);
const yearLevelForm = ref({
    year_level_id: "",
    remarks: "",
});

const studentForm = ref({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    email: "",
    phone_no: "",
    date_of_birth: "",
    gender: "",
    lrn: "",
    guardian_full_name: "",
    guardian_contact_no: "",
    password: "",
});

const resetStudentForm = () => {
    studentForm.value = {
        first_name: "",
        middle_name: "",
        last_name: "",
        suffix: "",
        email: "",
        phone_no: "",
        date_of_birth: "",
        gender: "",
        lrn: "",
        guardian_full_name: "",
        guardian_contact_no: "",
        password: "",
    };
};

const getInitials = (user) => {
    if (!user) return "?";
    return (
        (user.first_name?.charAt(0) || "") + (user.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const formatDateFull = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
};

const studentYearLevelName = (student) =>
    student.current_enrollment?.year_level?.name ||
    student.promotion?.current_year_level?.name ||
    student.promotion?.source_year_level?.name ||
    "";

const studentSectionName = (student) =>
    student.current_enrollment?.section?.name || "";

const studentYearLevelId = (student) =>
    student.current_enrollment?.year_level_id ||
    student.promotion?.current_year_level?.id ||
    student.promotion?.source_year_level?.id ||
    null;

const availableFilterSections = computed(() => {
    if (yearLevelFilter.value === "all") {
        return props.sections;
    }

    return props.sections.filter(
        (section) =>
            String(section.year_level_id) === String(yearLevelFilter.value),
    );
});

const filteredStudents = computed(() => {
    let result = props.students;

    if (yearLevelFilter.value !== "all") {
        result = result.filter(
            (student) =>
                String(studentYearLevelId(student)) ===
                String(yearLevelFilter.value),
        );
    }

    if (sectionFilter.value !== "all") {
        result = result.filter(
            (student) =>
                String(student.current_enrollment?.section_id) ===
                String(sectionFilter.value),
        );
    }

    if (genderFilter.value !== "all") {
        result = result.filter(
            (student) => student.gender === genderFilter.value,
        );
    }

    if (enrollmentStatusFilter.value !== "all") {
        result = result.filter((student) => {
            const status = student.current_enrollment?.status || "none";
            return status === enrollmentStatusFilter.value;
        });
    }

    if (promotionFilter.value !== "all") {
        result = result.filter(
            (student) =>
                (student.promotion?.status || "no_record") ===
                promotionFilter.value,
        );
    }

    if (studentSearch.value) {
        const search = studentSearch.value.toLowerCase();
        result = result.filter(
            (student) =>
                student.first_name?.toLowerCase().includes(search) ||
                student.middle_name?.toLowerCase().includes(search) ||
                student.last_name?.toLowerCase().includes(search) ||
                student.lrn?.toLowerCase().includes(search) ||
                student.email?.toLowerCase().includes(search),
        );
    }

    return result;
});

watch(yearLevelFilter, () => {
    sectionFilter.value = "all";
});

const eligibleCount = computed(
    () =>
        props.students.filter((student) => student.promotion?.status === "eligible")
            .length,
);
const incompleteCount = computed(
    () =>
        props.students.filter(
            (student) => student.promotion?.status === "incomplete",
        ).length,
);
const failedCount = computed(
    () =>
        props.students.filter((student) => student.promotion?.status === "failed")
            .length,
);
const promotedCount = computed(
    () =>
        props.students.filter(
            (student) => student.promotion?.status === "already_promoted",
        ).length,
);

const filteredEligibleStudents = computed(() =>
    filteredStudents.value.filter((student) => student.promotion?.can_promote),
);

const changeableYearLevels = computed(() => props.yearLevels || []);

const isSameCurrentYearLevel = computed(() => {
    const currentId = studentToChange.value?.current_enrollment?.year_level_id;
    if (!currentId || !yearLevelForm.value.year_level_id) {
        return false;
    }

    return String(currentId) === String(yearLevelForm.value.year_level_id);
});

const promotionLabel = (status) => {
    return (
        {
            eligible: "Eligible",
            already_promoted: "Promoted",
            completed: "Completed",
            failed: "Failed",
            incomplete: "Incomplete",
            no_record: "No record",
        }[status] || "No record"
    );
};

const isYearLevelAllowed = (yearLevelId) => {
    const allowed = studentToChange.value?.promotion?.allowed_year_level_ids || [];
    return allowed.map(String).includes(String(yearLevelId));
};

const openStudentModal = (mode, student = null) => {
    studentModalMode.value = mode;
    if (mode === "edit" && student) {
        selectedStudent.value = student;
        studentForm.value = {
            first_name: student.first_name || "",
            middle_name: student.middle_name || "",
            last_name: student.last_name || "",
            suffix: student.suffix || "",
            email: student.email || "",
            phone_no: student.phone_no || "",
            date_of_birth: student.date_of_birth
                ? student.date_of_birth.split("T")[0]
                : "",
            gender: student.gender || "",
            lrn: student.lrn || "",
            guardian_full_name: student.guardian_full_name || "",
            guardian_contact_no: student.guardian_contact_no || "",
            password: "",
        };
    } else if (mode === "add") {
        resetStudentForm();
        selectedStudent.value = null;
    }
    showStudentModal.value = true;
};

const viewStudent = (student) => {
    selectedStudent.value = student;
    studentModalMode.value = "view";
    showStudentModal.value = true;
};

const closeStudentModal = () => {
    showStudentModal.value = false;
    resetStudentForm();
    selectedStudent.value = null;
};

const formatLRN = (e) => {
    studentForm.value.lrn = e.target.value.replace(/\D/g, "").slice(0, 12);
};

const submitStudentForm = () => {
    isSubmitting.value = true;

    if (studentModalMode.value === "add") {
        router.post("/admin/students", studentForm.value, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Student created successfully!");
                closeStudentModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        });
    } else if (studentModalMode.value === "edit" && selectedStudent.value) {
        router.put(
            `/admin/students/${selectedStudent.value.id}`,
            studentForm.value,
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success("Student updated successfully!");
                    closeStudentModal();
                },
                onError: (errors) => {
                    const firstError = Object.values(errors)[0];
                    toast.error(firstError);
                },
                onFinish: () => {
                    isSubmitting.value = false;
                },
            },
        );
    }
};

const confirmDeleteStudent = (student) => {
    studentToDelete.value = student;
    showDeleteModal.value = true;
};

const openPromoteModal = (student) => {
    if (!student?.promotion?.can_promote) {
        toast.error(
            student?.promotion?.message ||
                "This student has not passed the previous year level.",
        );
        return;
    }

    studentToPromote.value = student;
    promotionRemarks.value = "";
    showPromoteModal.value = true;
    showStudentModal.value = false;
};

const closePromoteModal = () => {
    showPromoteModal.value = false;
    studentToPromote.value = null;
    promotionRemarks.value = "";
};

const promoteStudent = () => {
    if (!studentToPromote.value) return;
    isSubmitting.value = true;

    router.post(
        `/admin/students/${studentToPromote.value.id}/promote`,
        { remarks: promotionRemarks.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Student promoted successfully.");
                closePromoteModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openYearLevelModal = (student) => {
    studentToChange.value = student;

    let defaultYearLevelId = "";
    if (
        student.promotion?.can_promote &&
        student.promotion?.next_year_level?.id
    ) {
        defaultYearLevelId = String(student.promotion.next_year_level.id);
    } else if (student.current_enrollment?.year_level_id) {
        defaultYearLevelId = String(student.current_enrollment.year_level_id);
    } else if (student.promotion?.source_year_level?.id) {
        defaultYearLevelId = String(student.promotion.source_year_level.id);
    }

    yearLevelForm.value = {
        year_level_id: defaultYearLevelId,
        remarks: "",
    };
    showYearLevelModal.value = true;
    showStudentModal.value = false;
};

const closeYearLevelModal = () => {
    showYearLevelModal.value = false;
    studentToChange.value = null;
    yearLevelForm.value = { year_level_id: "", remarks: "" };
};

const submitYearLevelChange = () => {
    if (!studentToChange.value || !yearLevelForm.value.year_level_id) return;
    isSubmitting.value = true;

    router.put(
        `/admin/students/${studentToChange.value.id}/year-level`,
        yearLevelForm.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Year level updated successfully.");
                closeYearLevelModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openBulkPromoteModal = () => {
    if (filteredEligibleStudents.value.length === 0) {
        toast.error("No eligible students in the current list.");
        return;
    }

    showBulkPromoteModal.value = true;
};

const promoteEligibleStudents = () => {
    const studentIds = filteredEligibleStudents.value.map(
        (student) => student.id,
    );
    if (studentIds.length === 0) return;

    isSubmitting.value = true;
    router.post(
        "/admin/students/promote-selected",
        { student_ids: studentIds },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Eligible students were promoted.");
                showBulkPromoteModal.value = false;
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError);
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const deleteStudent = () => {
    if (!studentToDelete.value) return;
    isSubmitting.value = true;

    router.delete(`/admin/students/${studentToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success("Student deleted successfully!");
            showDeleteModal.value = false;
            studentToDelete.value = null;
        },
        onError: () => {
            toast.error("Failed to delete student.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.filter-group {
    flex-wrap: wrap;
    justify-content: flex-end;
}

.text-muted {
    color: #555;
    font-style: italic;
}

.promotion-cell {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.promotion-cell small {
    color: #555;
    font-size: 0.75rem;
}

.status-badge.eligible {
    color: #1f6b3a;
    border-color: #1f6b3a;
}

.status-badge.failed {
    color: #9b1c1c;
    border-color: #9b1c1c;
}

.status-badge.incomplete,
.status-badge.no_record {
    color: #9a6700;
    border-color: #9a6700;
}

.status-badge.already_promoted,
.status-badge.completed {
    color: #003366;
    border-color: #c9a227;
}

.btn-icon.promote:hover:not(:disabled) {
    background: #1f6b3a;
    border-color: #1f6b3a;
    color: #fff;
}

.btn-icon:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.modal-footer-inline {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}

.form-group textarea {
    width: 100%;
    padding: 0.5rem 0.6rem;
    border: 1px solid #bdbdbd;
    font-size: 0.9rem;
    resize: vertical;
}
</style>
