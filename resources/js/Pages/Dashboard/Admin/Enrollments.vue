<template>
    <AdminLayout
        title="Enrollments"
        pageTitle="Enrollments"
        currentPage="enrollments"
        :user="user"
    >
        <div class="content-section">
            <div class="gov-pagehead">
                <p class="gov-kicker">
                    Tambo National High School — Buhi, Camarines Sur
                </p>
                <div class="gov-pagehead-row">
                    <h2>Enrollments</h2>
                </div>
            </div>

            <div class="gov-stat-row">
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Pending</div>
                    <div class="gov-stat-value">{{ pendingCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Approved</div>
                    <div class="gov-stat-value">{{ approvedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Enrolled</div>
                    <div class="gov-stat-value">{{ enrolledCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Rejected</div>
                    <div class="gov-stat-value">{{ rejectedCount }}</div>
                </div>
                <div class="gov-stat-box">
                    <div class="gov-stat-label">Dropped</div>
                    <div class="gov-stat-value">{{ droppedCount }}</div>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="section-header">
                <div class="header-actions">
                    <div class="search-box">
                        <Search :size="18" class="search-icon" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by student name or LRN..."
                            class="search-input"
                        />
                    </div>
                    <div class="filter-group">
                        <select v-model="statusFilter" class="filter-select">
                            <option value="all">All Enrollment Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="enrolled">Enrolled</option>
                            <option value="rejected">Rejected</option>
                            <option value="dropped">Dropped</option>
                        </select>
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
                        <button
                            type="button"
                            class="btn-primary"
                            @click="openBulkAssignModal"
                        >
                            <Layers :size="18" />
                            Assign Sections
                        </button>
                        <button
                            type="button"
                            class="btn-primary"
                            @click="openReshuffleModal"
                        >
                            <Shuffle :size="18" />
                            Reshuffle by Grades
                        </button>
                        <button
                            type="button"
                            class="btn-primary"
                            :disabled="selectedIds.length === 0"
                            @click="openBulkApproveModal"
                        >
                            <Check :size="18" />
                            Approve Selected
                            <span
                                v-if="selectedIds.length"
                                class="selection-count"
                            >
                                ({{ selectedIds.length }})
                            </span>
                        </button>
                        <button
                            type="button"
                            class="btn-primary"
                            :disabled="selectedIds.length === 0"
                            @click="openBulkEnrollModal"
                        >
                            <UserCheck :size="18" />
                            Enroll Selected
                            <span
                                v-if="selectedIds.length"
                                class="selection-count"
                            >
                                ({{ selectedIds.length }})
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Enrollments Table -->
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="col-check">
                                <input
                                    type="checkbox"
                                    :checked="allFilteredSelected"
                                    :indeterminate.prop="selectAllIndeterminate"
                                    :disabled="filteredEnrollments.length === 0"
                                    @change="toggleSelectAllFiltered"
                                    title="Select all visible students"
                                />
                            </th>
                            <th>Student</th>
                            <th>Year Level</th>
                            <th>Section</th>
                            <th>Admission GWA</th>
                            <th>Teacher GWA</th>
                            <th>Admission Status</th>
                            <th>Enrollment Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="enrollment in paginatedEnrollments"
                            :key="enrollment.id"
                        >
                            <td class="col-check">
                                <input
                                    type="checkbox"
                                    :checked="isSelected(enrollment.id)"
                                    @change="toggleSelect(enrollment.id)"
                                    :title="
                                        canEnroll(enrollment)
                                            ? 'Select student'
                                            : 'Select student (not yet eligible to enroll)'
                                    "
                                />
                            </td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar-sm">
                                        {{ getInitials(enrollment.user) }}
                                    </div>
                                    <div class="user-info-cell">
                                        <span class="user-name-cell">
                                            {{ enrollment.user?.last_name }},
                                            {{ enrollment.user?.first_name }}
                                            {{
                                                enrollment.user?.middle_name
                                                    ? enrollment.user.middle_name.charAt(
                                                          0,
                                                      ) + "."
                                                    : ""
                                            }}
                                        </span>
                                        <span class="lrn-badge">{{
                                            enrollment.user?.lrn
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="year-level-badge">
                                    {{ enrollment.year_level?.name || "-" }}
                                </span>
                            </td>
                            <td>
                                <span
                                    v-if="enrollment.section"
                                    class="section-badge"
                                >
                                    {{ enrollment.section?.name }}
                                </span>
                                <span v-else class="text-muted"
                                    >Not assigned</span
                                >
                            </td>
                            <td>
                                <span
                                    class="gwa-display"
                                    :class="
                                        getGwaClass(enrollment.previous_gwa)
                                    "
                                >
                                    {{
                                        enrollment.previous_gwa
                                            ? parseFloat(
                                                  enrollment.previous_gwa,
                                              ).toFixed(2)
                                            : "-"
                                    }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="gwa-display"
                                    :class="getGwaClass(enrollment.teacher_gwa)"
                                >
                                    {{
                                        enrollment.teacher_gwa
                                            ? parseFloat(
                                                  enrollment.teacher_gwa,
                                              ).toFixed(2)
                                            : "-"
                                    }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="
                                        enrollment.user?.admission_status ||
                                        'pending'
                                    "
                                >
                                    {{
                                        enrollment.user?.admission_status ||
                                        "—"
                                    }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="status-badge"
                                    :class="enrollment.status"
                                >
                                    {{ enrollment.status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button
                                        class="btn-icon view"
                                        @click="viewEnrollment(enrollment)"
                                        title="View Details"
                                    >
                                        <Eye :size="16" />
                                    </button>
                                    <button
                                        v-if="enrollment.status === 'pending'"
                                        class="btn-icon approve"
                                        @click="openApproveModal(enrollment)"
                                        title="Approve"
                                    >
                                        <Check :size="16" />
                                    </button>
                                    <button
                                        v-if="enrollment.status === 'pending'"
                                        class="btn-icon reject"
                                        @click="openRejectModal(enrollment)"
                                        title="Reject"
                                    >
                                        <XCircle :size="16" />
                                    </button>
                                    <button
                                        v-if="canAssignSection(enrollment)"
                                        class="btn-icon edit"
                                        @click="
                                            openAssignSectionModal(enrollment)
                                        "
                                        title="Assign Section"
                                    >
                                        <Layers :size="16" />
                                    </button>
                                    <button
                                        v-if="canTransferSection(enrollment)"
                                        class="btn-icon transfer"
                                        @click="
                                            openTransferSectionModal(
                                                enrollment,
                                            )
                                        "
                                        title="Transfer Section"
                                    >
                                        <ArrowRightLeft :size="16" />
                                    </button>
                                    <button
                                        v-if="canEnroll(enrollment)"
                                        class="btn-icon enroll"
                                        @click="openEnrollModal(enrollment)"
                                        title="Enroll Student"
                                    >
                                        <UserCheck :size="16" />
                                    </button>
                                    <button
                                        v-if="canDrop(enrollment)"
                                        class="btn-icon reject"
                                        @click="openDropModal(enrollment)"
                                        title="Mark as drop-out"
                                    >
                                        <UserMinus :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredEnrollments.length === 0">
                            <td colspan="9" class="empty-table">
                                <div class="empty-message">
                                    <Users :size="40" />
                                    <p>No enrollments found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="table-footer">
                <span class="record-count">
                    Showing {{ pageStart }}-{{ pageEnd }} of
                    {{ filteredEnrollments.length }} enrollments
                    <template v-if="selectedIds.length">
                        · {{ selectedIds.length }} selected ·
                        {{ selectedApprovableCount }} ready to approve ·
                        {{ selectedEnrollableCount }} ready to enroll
                    </template>
                </span>
                <nav
                    v-if="filteredEnrollments.length > 0"
                    class="pagination"
                    aria-label="Enrollments pagination"
                >
                    <button
                        type="button"
                        class="page-btn"
                        :disabled="currentPage === 1"
                        title="Previous page"
                        @click="goToPage(currentPage - 1)"
                    >
                        <ChevronLeft :size="16" />
                    </button>
                    <button
                        v-for="page in visiblePageNumbers"
                        :key="page"
                        type="button"
                        class="page-btn"
                        :class="{ active: page === currentPage }"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>
                    <button
                        type="button"
                        class="page-btn"
                        :disabled="currentPage === totalPages"
                        title="Next page"
                        @click="goToPage(currentPage + 1)"
                    >
                        <ChevronRight :size="16" />
                    </button>
                </nav>
            </div>
        </div>

        <!-- View Enrollment Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showViewModal"
                    class="modal-overlay"
                    @click.self="closeViewModal"
                >
                    <div class="modal-container large">
                        <div class="modal-header enrollment-header">
                            <h3>
                                <Eye :size="22" />
                                Enrollment Details
                            </h3>
                            <button class="close-btn" @click="closeViewModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="view-details">
                                <div class="detail-header">
                                    <div class="detail-avatar">
                                        {{
                                            getInitials(
                                                selectedEnrollment?.user,
                                            )
                                        }}
                                    </div>
                                    <div class="detail-title">
                                        <h4>
                                            {{
                                                selectedEnrollment?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                selectedEnrollment?.user
                                                    ?.middle_name
                                            }}
                                            {{
                                                selectedEnrollment?.user
                                                    ?.last_name
                                            }}
                                        </h4>
                                        <span class="lrn-badge large"
                                            >LRN:
                                            {{
                                                selectedEnrollment?.user?.lrn
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <label>School Year</label>
                                        <span>{{
                                            selectedEnrollment?.school_year ||
                                            currentSchoolYear
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Admission Status</label>
                                        <span
                                            class="status-badge"
                                            :class="
                                                selectedEnrollment?.user
                                                    ?.admission_status ||
                                                'pending'
                                            "
                                        >
                                            {{
                                                selectedEnrollment?.user
                                                    ?.admission_status || "—"
                                            }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Enrollment Status</label>
                                        <span
                                            class="status-badge"
                                            :class="selectedEnrollment?.status"
                                        >
                                            {{ selectedEnrollment?.status }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Year Level</label>
                                        <span>{{
                                            selectedEnrollment?.year_level
                                                ?.name || "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Section</label>
                                        <span>{{
                                            selectedEnrollment?.section?.name ||
                                            "Not assigned"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Previous GWA</label>
                                        <span
                                            class="gwa-display"
                                            :class="
                                                getGwaClass(
                                                    selectedEnrollment?.previous_gwa,
                                                )
                                            "
                                        >
                                            {{
                                                selectedEnrollment?.previous_gwa
                                                    ? parseFloat(
                                                          selectedEnrollment.previous_gwa,
                                                      ).toFixed(2)
                                                    : "N/A"
                                            }}
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Enrollment Type</label>
                                        <span>{{
                                            selectedEnrollment?.enrollment_type ||
                                            "-"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Previous School</label>
                                        <span>{{
                                            selectedEnrollment?.previous_school ||
                                            "N/A"
                                        }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <label>Enrolled At</label>
                                        <span>{{
                                            selectedEnrollment?.enrolled_at
                                                ? formatDate(
                                                      selectedEnrollment.enrolled_at,
                                                  )
                                                : "N/A"
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="selectedEnrollment?.remarks"
                                        class="detail-item full-width"
                                    >
                                        <label>Remarks</label>
                                        <p class="description-text">
                                            {{ selectedEnrollment.remarks }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeViewModal"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Approve Confirmation Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showApproveModal"
                    class="modal-overlay"
                    @click.self="closeApproveModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header success-header">
                            <h3>
                                <CheckCircle :size="22" />
                                Approve Enrollment
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeApproveModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <div class="confirm-icon success">
                                    <Check :size="40" />
                                </div>
                                <p>
                                    Are you sure you want to approve this
                                    enrollment?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{
                                            enrollmentToApprove?.user
                                                ?.first_name
                                        }}
                                        {{
                                            enrollmentToApprove?.user?.last_name
                                        }}</strong
                                    >
                                    <span>{{
                                        enrollmentToApprove?.year_level?.name
                                    }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeApproveModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="isSubmitting"
                                @click="approveEnrollment"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Approve
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Reject Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showRejectModal"
                    class="modal-overlay"
                    @click.self="closeRejectModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header danger">
                            <h3>
                                <XCircle :size="22" />
                                Reject Enrollment
                            </h3>
                            <button class="close-btn" @click="closeRejectModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="reject-form">
                                <div class="reject-info">
                                    <p>
                                        You are about to reject the enrollment
                                        for:
                                    </p>
                                    <div class="delete-student-info">
                                        <strong
                                            >{{
                                                enrollmentToReject?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                enrollmentToReject?.user
                                                    ?.last_name
                                            }}</strong
                                        >
                                        <span>{{
                                            enrollmentToReject?.year_level?.name
                                        }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label
                                        >Remarks
                                        <span class="required">*</span></label
                                    >
                                    <textarea
                                        v-model="rejectRemarks"
                                        placeholder="Enter reason for rejection..."
                                        rows="4"
                                        required
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeRejectModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="
                                    isSubmitting || !rejectRemarks.trim()
                                "
                                @click="rejectEnrollment"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Assign Section Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showAssignSectionModal"
                    class="modal-overlay"
                    @click.self="closeAssignSectionModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header section-header">
                            <h3>
                                <Layers :size="22" />
                                Assign Section
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeAssignSectionModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="assign-section-form">
                                <div class="assign-info">
                                    <p>Assign a section to:</p>
                                    <div class="delete-student-info">
                                        <strong
                                            >{{
                                                enrollmentToAssign?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                enrollmentToAssign?.user
                                                    ?.last_name
                                            }}</strong
                                        >
                                        <span>{{
                                            enrollmentToAssign?.year_level?.name
                                        }}</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label
                                        >Section
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        v-model="selectedSectionId"
                                        required
                                    >
                                        <option value="">
                                            Select a section
                                        </option>
                                        <option
                                            v-for="section in availableSections"
                                            :key="section.id"
                                            :value="section.id"
                                        >
                                            {{ section.name }} ({{
                                                section.year_level?.name
                                            }})
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeAssignSectionModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="isSubmitting || !selectedSectionId"
                                @click="assignSection"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Assign Section
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Transfer Section Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showTransferSectionModal"
                    class="modal-overlay"
                    @click.self="closeTransferSectionModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header section-header">
                            <h3>
                                <ArrowRightLeft :size="22" />
                                Transfer Section
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeTransferSectionModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="assign-section-form">
                                <div class="transfer-note">
                                    <p>
                                        This student is already assigned to a
                                        section. Transfer them only if they
                                        really need to move.
                                    </p>
                                    <p>
                                        Do you really need to transfer
                                        <strong
                                            >{{
                                                enrollmentToTransfer?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                enrollmentToTransfer?.user
                                                    ?.last_name
                                            }}</strong
                                        >
                                        to another section?
                                    </p>
                                </div>
                                <div class="assign-info">
                                    <div class="delete-student-info">
                                        <strong
                                            >{{
                                                enrollmentToTransfer?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                enrollmentToTransfer?.user
                                                    ?.last_name
                                            }}</strong
                                        >
                                        <span>{{
                                            enrollmentToTransfer?.year_level
                                                ?.name
                                        }}</span>
                                        <span>
                                            Current section:
                                            {{
                                                enrollmentToTransfer?.section
                                                    ?.name || "—"
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label
                                        >New Section
                                        <span class="required">*</span></label
                                    >
                                    <select
                                        v-model="selectedTransferSectionId"
                                        required
                                        :disabled="
                                            availableTransferSections.length ===
                                            0
                                        "
                                    >
                                        <option value="">
                                            {{
                                                availableTransferSections.length
                                                    ? "Select a different section"
                                                    : "No other section is available"
                                            }}
                                        </option>
                                        <option
                                            v-for="section in availableTransferSections"
                                            :key="section.id"
                                            :value="section.id"
                                        >
                                            {{ section.name }} ({{
                                                section.year_level?.name
                                            }})
                                        </option>
                                    </select>
                                </div>
                                <label class="transfer-confirm">
                                    <input
                                        v-model="confirmTransfer"
                                        type="checkbox"
                                    />
                                    <span
                                        >Yes, I need to transfer this student
                                        to another section.</span
                                    >
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeTransferSectionModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="
                                    isSubmitting ||
                                    !selectedTransferSectionId ||
                                    !confirmTransfer ||
                                    availableTransferSections.length === 0
                                "
                                @click="transferSection"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Transfer Student
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Enroll Student Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showEnrollModal"
                    class="modal-overlay"
                    @click.self="closeEnrollModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header enrollment-header">
                            <h3>
                                <UserCheck :size="22" />
                                Enroll Student
                            </h3>
                            <button class="close-btn" @click="closeEnrollModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <div class="confirm-icon success">
                                    <UserCheck :size="40" />
                                </div>
                                <p>
                                    Enroll this student for
                                    {{ currentSchoolYear }}?
                                </p>
                                <div class="delete-student-info">
                                    <strong
                                        >{{
                                            enrollmentToEnroll?.user
                                                ?.first_name
                                        }}
                                        {{
                                            enrollmentToEnroll?.user?.last_name
                                        }}</strong
                                    >
                                    <span>{{
                                        enrollmentToEnroll?.year_level?.name
                                    }}</span>
                                    <span v-if="enrollmentToEnroll?.section">
                                        Section:
                                        {{ enrollmentToEnroll.section.name }}
                                    </span>
                                    <span v-else class="text-muted">
                                        Section not assigned yet
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeEnrollModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="isSubmitting"
                                @click="enrollStudent"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Enroll Student
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Approve Selected Students Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showBulkApproveModal"
                    class="modal-overlay"
                    @click.self="closeBulkApproveModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header success-header">
                            <h3>
                                <CheckCircle :size="22" />
                                Approve Selected Students
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeBulkApproveModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <div class="confirm-icon success">
                                    <Check :size="40" />
                                </div>
                                <p>
                                    Approve
                                    {{ selectedApprovableCount }} selected
                                    student(s)?
                                </p>
                                <div class="delete-student-info">
                                    <strong>
                                        {{ selectedApprovableCount }} ready to
                                        approve
                                    </strong>
                                    <span
                                        v-if="selectedApproveSkippedCount > 0"
                                        class="text-muted"
                                    >
                                        {{ selectedApproveSkippedCount }}
                                        selected student(s) will be skipped
                                        because they are not pending.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeBulkApproveModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-success"
                                :disabled="
                                    isSubmitting ||
                                    selectedApprovableCount === 0
                                "
                                @click="approveSelectedStudents"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Approve Selected
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Enroll Selected Students Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showBulkEnrollModal"
                    class="modal-overlay"
                    @click.self="closeBulkEnrollModal"
                >
                    <div class="modal-container small">
                        <div class="modal-header enrollment-header">
                            <h3>
                                <UserCheck :size="22" />
                                Enroll Selected Students
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeBulkEnrollModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="confirm-message">
                                <div class="confirm-icon success">
                                    <UserCheck :size="40" />
                                </div>
                                <p>
                                    Enroll
                                    {{ selectedEnrollableCount }} selected
                                    student(s) for {{ currentSchoolYear }}?
                                </p>
                                <div class="delete-student-info">
                                    <strong>
                                        {{ selectedEnrollableCount }} ready to
                                        enroll
                                    </strong>
                                    <span
                                        v-if="selectedSkippedCount > 0"
                                        class="text-muted"
                                    >
                                        {{ selectedSkippedCount }} selected
                                        student(s) will be skipped because they
                                        are not yet approved.
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeBulkEnrollModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="
                                    isSubmitting || selectedEnrollableCount === 0
                                "
                                @click="enrollSelectedStudents"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Enroll Selected
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Custom Section Assignment Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showBulkAssignModal"
                    class="modal-overlay"
                    @click.self="closeBulkAssignModal"
                >
                    <div class="modal-container">
                        <div class="modal-header section-header">
                            <h3>
                                <Layers :size="22" />
                                Assign Sections
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeBulkAssignModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="assign-section-form">
                                <p class="bulk-assign-intro">
                                    Assign sections to approved students who do
                                    not have a section yet. Pending students
                                    must be approved first.
                                </p>
                                <div class="form-group">
                                    <label>Year Level</label>
                                    <select v-model="bulkYearLevelId">
                                        <option value="all">
                                            All Year Levels
                                        </option>
                                        <option
                                            v-for="level in yearLevels"
                                            :key="level.id"
                                            :value="level.id"
                                        >
                                            {{ level.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label
                                        >Assignment Method
                                        <span class="required">*</span></label
                                    >
                                    <div class="method-options">
                                        <label
                                            class="method-option"
                                            :class="{
                                                selected:
                                                    bulkAssignMethod === 'gwa',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="gwa"
                                                v-model="bulkAssignMethod"
                                            />
                                            <span>
                                                <strong>Based on GWA</strong>
                                                Highest GWA students are grouped
                                                together by rank. The next
                                                section receives the next rank
                                                group.
                                            </span>
                                        </label>
                                        <label
                                            class="method-option"
                                            :class="{
                                                selected:
                                                    bulkAssignMethod ===
                                                    'mixed',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="mixed"
                                                v-model="bulkAssignMethod"
                                            />
                                            <span>
                                                <strong>Mixed</strong>
                                                Each section gets a mix of high
                                                GWA and low GWA students so
                                                classes are balanced.
                                            </span>
                                        </label>
                                        <label
                                            class="method-option"
                                            :class="{
                                                selected:
                                                    bulkAssignMethod ===
                                                    'shuffle',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="shuffle"
                                                v-model="bulkAssignMethod"
                                            />
                                            <span>
                                                <strong>Shuffle</strong>
                                                Students are assigned at random
                                                and spread evenly across
                                                sections.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <label class="method-option">
                                    <input
                                        type="checkbox"
                                        v-model="useTeacherGrades"
                                    />
                                    <span>
                                        <strong>Use teacher-entered grades</strong>
                                        Rank students from encoded final grades
                                        instead of admission GWA.
                                    </span>
                                </label>
                                <p class="bulk-assign-count">
                                    {{ bulkAssignCount }} student(s) ready for
                                    section assignment.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeBulkAssignModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="
                                    isSubmitting || bulkAssignCount === 0
                                "
                                @click="submitBulkAssign"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Assign Sections
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showReshuffleModal"
                    class="modal-overlay"
                    @click.self="closeReshuffleModal"
                >
                    <div class="modal-container">
                        <div class="modal-header section-header">
                            <h3>
                                <Shuffle :size="22" />
                                Reshuffle Sections by Grades
                            </h3>
                            <button
                                class="close-btn"
                                @click="closeReshuffleModal"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="assign-section-form">
                                <p class="bulk-assign-intro">
                                    Reassign every approved or enrolled student
                                    in the selected year level using the grades
                                    already encoded by teachers. This replaces
                                    current section assignments.
                                </p>
                                <div class="form-group">
                                    <label>Year Level <span class="required">*</span></label>
                                    <select v-model="reshuffleYearLevelId">
                                        <option
                                            v-for="level in yearLevels"
                                            :key="level.id"
                                            :value="level.id"
                                        >
                                            {{ level.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Assignment Method</label>
                                    <div class="method-options">
                                        <label
                                            class="method-option"
                                            :class="{
                                                selected:
                                                    reshuffleMethod === 'gwa',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="gwa"
                                                v-model="reshuffleMethod"
                                            />
                                            <span>
                                                <strong>Based on GWA</strong>
                                                Highest teacher GWA students are
                                                grouped together.
                                            </span>
                                        </label>
                                        <label
                                            class="method-option"
                                            :class="{
                                                selected:
                                                    reshuffleMethod ===
                                                    'mixed',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="mixed"
                                                v-model="reshuffleMethod"
                                            />
                                            <span>
                                                <strong>Mixed</strong>
                                                Each section gets a mix of high
                                                and low teacher GWA.
                                            </span>
                                        </label>
                                        <label
                                            class="method-option"
                                            :class="{
                                                selected:
                                                    reshuffleMethod ===
                                                    'shuffle',
                                            }"
                                        >
                                            <input
                                                type="radio"
                                                value="shuffle"
                                                v-model="reshuffleMethod"
                                            />
                                            <span>
                                                <strong>Shuffle</strong>
                                                Even random distribution after
                                                ranking is ignored.
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <p class="bulk-assign-count">
                                    {{ reshuffleCount }} student(s) in this year
                                    level will be reassigned.
                                    {{ reshuffleGradedCount }} already have
                                    teacher-entered grades.
                                </p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeReshuffleModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-primary"
                                :disabled="
                                    isSubmitting || reshuffleCount === 0
                                "
                                @click="submitReshuffle"
                            >
                                <Loader2
                                    v-if="isSubmitting"
                                    :size="18"
                                    class="spin"
                                />
                                Reshuffle Sections
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showDropModal"
                    class="modal-overlay"
                    @click.self="closeDropModal"
                >
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3>Mark as drop-out</h3>
                            <button class="close-btn" @click="closeDropModal">
                                <X :size="20" />
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Mark
                                <strong>
                                    {{ enrollmentToDrop?.user?.last_name }},
                                    {{ enrollmentToDrop?.user?.first_name }}
                                </strong>
                                as a drop-out for this school year? This is
                                included in the enrollment summary dropout rate.
                            </p>
                            <label>Remarks</label>
                            <textarea
                                v-model="dropRemarks"
                                rows="3"
                                placeholder="Reason for leaving, if known"
                            ></textarea>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn-secondary"
                                @click="closeDropModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="btn-danger"
                                :disabled="isSubmitting"
                                @click="submitDrop"
                            >
                                Confirm drop-out
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmModal
            :show="showReshuffleConfirm"
            title="Reshuffle Sections"
            message="This will reassign all students in the selected year level based on teacher-entered grades. Continue?"
            confirm-label="Reshuffle"
            :danger="false"
            :busy="isSubmitting"
            @cancel="showReshuffleConfirm = false"
            @confirm="confirmReshuffle"
        />
    </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import {
    Users,
    Search,
    Eye,
    Check,
    XCircle,
    Layers,
    CheckCircle,
    UserCheck,
    UserMinus,
    Shuffle,
    ArrowRightLeft,
    X,
    Loader2,
    ChevronLeft,
    ChevronRight,
} from "lucide-vue-next";

const toast = useToast();

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    enrollments: {
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

// State
const searchQuery = ref("");
const statusFilter = ref("all");
const yearLevelFilter = ref("all");
const isSubmitting = ref(false);
const selectedIds = ref([]);
const currentPage = ref(1);
const pageSize = 15;

// View Modal
const showViewModal = ref(false);
const selectedEnrollment = ref(null);

// Approve Modal
const showApproveModal = ref(false);
const enrollmentToApprove = ref(null);

// Reject Modal
const showRejectModal = ref(false);
const enrollmentToReject = ref(null);
const rejectRemarks = ref("");

// Assign Section Modal
const showAssignSectionModal = ref(false);
const enrollmentToAssign = ref(null);
const selectedSectionId = ref("");

// Transfer Section Modal
const showTransferSectionModal = ref(false);
const enrollmentToTransfer = ref(null);
const selectedTransferSectionId = ref("");
const confirmTransfer = ref(false);

// Enroll Modal
const showEnrollModal = ref(false);
const enrollmentToEnroll = ref(null);
const showBulkEnrollModal = ref(false);
const showBulkApproveModal = ref(false);

// Bulk Assign Sections Modal
const showBulkAssignModal = ref(false);
const bulkAssignMethod = ref("gwa");
const bulkYearLevelId = ref("all");
const useTeacherGrades = ref(false);
const showReshuffleModal = ref(false);
const showReshuffleConfirm = ref(false);
const reshuffleMethod = ref("gwa");
const reshuffleYearLevelId = ref("");
const showDropModal = ref(false);
const enrollmentToDrop = ref(null);
const dropRemarks = ref("");

// Computed
const pendingCount = computed(() => {
    return props.enrollments.filter((e) => e.status === "pending").length;
});

const approvedCount = computed(() => {
    return props.enrollments.filter((e) => e.status === "approved").length;
});

const enrolledCount = computed(() => {
    return props.enrollments.filter((e) => e.status === "enrolled").length;
});

const rejectedCount = computed(() => {
    return props.enrollments.filter((e) => e.status === "rejected").length;
});

const droppedCount = computed(() => {
    return props.enrollments.filter((e) => e.status === "dropped").length;
});

const filteredEnrollments = computed(() => {
    let result = props.enrollments;

    // Filter by status
    if (statusFilter.value !== "all") {
        result = result.filter((e) => e.status === statusFilter.value);
    }

    // Filter by year level
    if (yearLevelFilter.value !== "all") {
        result = result.filter(
            (e) => e.year_level_id === yearLevelFilter.value,
        );
    }

    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter((e) => {
            const fullName =
                `${e.user?.first_name} ${e.user?.middle_name || ""} ${e.user?.last_name}`.toLowerCase();
            const lrn = e.user?.lrn?.toLowerCase() || "";
            return fullName.includes(query) || lrn.includes(query);
        });
    }

    return result;
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredEnrollments.value.length / pageSize));
});

const paginatedEnrollments = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredEnrollments.value.slice(start, start + pageSize);
});

const pageStart = computed(() => {
    if (filteredEnrollments.value.length === 0) return 0;
    return (currentPage.value - 1) * pageSize + 1;
});

const pageEnd = computed(() => {
    return Math.min(
        currentPage.value * pageSize,
        filteredEnrollments.value.length,
    );
});

const visiblePageNumbers = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    let start = Math.max(1, current - 2);
    let end = Math.min(total, start + 4);
    start = Math.max(1, end - 4);

    const pages = [];
    for (let page = start; page <= end; page++) {
        pages.push(page);
    }
    return pages;
});

watch([searchQuery, statusFilter, yearLevelFilter], () => {
    currentPage.value = 1;
});

watch(totalPages, (pages) => {
    if (currentPage.value > pages) {
        currentPage.value = pages;
    }
});

const availableSections = computed(() => {
    if (!enrollmentToAssign.value) return props.sections;
    return props.sections.filter(
        (s) => s.year_level_id === enrollmentToAssign.value.year_level_id,
    );
});

const availableTransferSections = computed(() => {
    if (!enrollmentToTransfer.value) return [];
    const currentSectionId = Number(
        enrollmentToTransfer.value.section_id ||
            enrollmentToTransfer.value.section?.id,
    );
    return props.sections.filter(
        (s) =>
            s.year_level_id === enrollmentToTransfer.value.year_level_id &&
            Number(s.id) !== currentSectionId,
    );
});

const canApprove = (enrollment) => {
    return enrollment.status === "pending";
};

const canEnroll = (enrollment) => {
    return (
        enrollment.status === "approved" &&
        enrollment.user?.admission_status === "approved"
    );
};

const hasAssignedSection = (enrollment) => {
    return Boolean(enrollment?.section_id || enrollment?.section?.id);
};

const canAssignSection = (enrollment) => {
    return (
        (enrollment.status === "approved" ||
            enrollment.status === "enrolled") &&
        !hasAssignedSection(enrollment)
    );
};

const canTransferSection = (enrollment) => {
    return (
        (enrollment.status === "approved" ||
            enrollment.status === "enrolled") &&
        hasAssignedSection(enrollment)
    );
};

const canDrop = (enrollment) => {
    return (
        enrollment.status === "approved" || enrollment.status === "enrolled"
    );
};

const isSelected = (id) => {
    return selectedIds.value.map((selected) => Number(selected)).includes(Number(id));
};

const toggleSelect = (id) => {
    const numericId = Number(id);
    if (isSelected(numericId)) {
        selectedIds.value = selectedIds.value.filter(
            (selected) => Number(selected) !== numericId,
        );
        return;
    }
    selectedIds.value = [...selectedIds.value, numericId];
};

const toggleSelectAllFiltered = () => {
    if (allFilteredSelected.value) {
        const visible = new Set(filteredEnrollmentIds.value);
        selectedIds.value = selectedIds.value.filter(
            (id) => !visible.has(Number(id)),
        );
        return;
    }

    selectedIds.value = [
        ...new Set([
            ...selectedIds.value.map((id) => Number(id)),
            ...filteredEnrollmentIds.value,
        ]),
    ];
};

const bulkAssignCount = computed(() => {
    return props.enrollments.filter((enrollment) => {
        const eligible =
            (enrollment.status === "approved" ||
                enrollment.status === "enrolled") &&
            !enrollment.section_id;
        if (!eligible) return false;
        if (bulkYearLevelId.value === "all") return true;
        return (
            String(enrollment.year_level_id) === String(bulkYearLevelId.value)
        );
    }).length;
});

const reshuffleCount = computed(() => {
    if (!reshuffleYearLevelId.value) return 0;
    return props.enrollments.filter((enrollment) => {
        return (
            (enrollment.status === "approved" ||
                enrollment.status === "enrolled") &&
            String(enrollment.year_level_id) ===
                String(reshuffleYearLevelId.value)
        );
    }).length;
});

const reshuffleGradedCount = computed(() => {
    if (!reshuffleYearLevelId.value) return 0;
    return props.enrollments.filter((enrollment) => {
        return (
            (enrollment.status === "approved" ||
                enrollment.status === "enrolled") &&
            String(enrollment.year_level_id) ===
                String(reshuffleYearLevelId.value) &&
            enrollment.teacher_gwa
        );
    }).length;
});

const selectedEnrollmentRecords = computed(() => {
    const ids = new Set(selectedIds.value.map((id) => Number(id)));
    return props.enrollments.filter((enrollment) => ids.has(Number(enrollment.id)));
});

const selectedEnrollableCount = computed(() => {
    return selectedEnrollmentRecords.value.filter((enrollment) =>
        canEnroll(enrollment),
    ).length;
});

const selectedApprovableCount = computed(() => {
    return selectedEnrollmentRecords.value.filter((enrollment) =>
        canApprove(enrollment),
    ).length;
});

const selectedApproveSkippedCount = computed(() => {
    return Math.max(
        0,
        selectedIds.value.length - selectedApprovableCount.value,
    );
});

const selectedSkippedCount = computed(() => {
    return Math.max(
        0,
        selectedIds.value.length - selectedEnrollableCount.value,
    );
});

const filteredEnrollmentIds = computed(() => {
    return filteredEnrollments.value.map((enrollment) => Number(enrollment.id));
});

const allFilteredSelected = computed(() => {
    return (
        filteredEnrollmentIds.value.length > 0 &&
        filteredEnrollmentIds.value.every((id) =>
            selectedIds.value.map((selected) => Number(selected)).includes(id),
        )
    );
});

const selectAllIndeterminate = computed(() => {
    const selectedVisible = filteredEnrollmentIds.value.filter((id) =>
        selectedIds.value.map((selected) => Number(selected)).includes(id),
    ).length;

    return (
        selectedVisible > 0 &&
        selectedVisible < filteredEnrollmentIds.value.length
    );
});

// Methods
const goToPage = (page) => {
    if (page < 1 || page > totalPages.value) return;
    currentPage.value = page;
};

const getInitials = (user) => {
    if (!user) return "?";
    return (
        (user.first_name?.charAt(0) || "") + (user.last_name?.charAt(0) || "")
    ).toUpperCase();
};

const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "long",
        day: "numeric",
        year: "numeric",
    });
};

const getGwaClass = (gwa) => {
    if (!gwa) return "";
    const value = parseFloat(gwa);
    if (value <= 1.5) return "excellent";
    if (value <= 2.0) return "good";
    if (value <= 2.5) return "average";
    return "below-average";
};

// View Modal
const viewEnrollment = (enrollment) => {
    selectedEnrollment.value = enrollment;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedEnrollment.value = null;
};

// Approve Modal
const openApproveModal = (enrollment) => {
    enrollmentToApprove.value = enrollment;
    showApproveModal.value = true;
};

const closeApproveModal = () => {
    showApproveModal.value = false;
    enrollmentToApprove.value = null;
};

const approveEnrollment = () => {
    if (!enrollmentToApprove.value) return;
    isSubmitting.value = true;

    router.post(
        `/admin/enrollments/${enrollmentToApprove.value.id}/approve`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Enrollment approved successfully!");
                closeApproveModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to approve enrollment.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

// Reject Modal
const openRejectModal = (enrollment) => {
    enrollmentToReject.value = enrollment;
    rejectRemarks.value = "";
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    enrollmentToReject.value = null;
    rejectRemarks.value = "";
};

const rejectEnrollment = () => {
    if (!enrollmentToReject.value || !rejectRemarks.value.trim()) return;
    isSubmitting.value = true;

    router.post(
        `/admin/enrollments/${enrollmentToReject.value.id}/reject`,
        {
            remarks: rejectRemarks.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Enrollment rejected.");
                closeRejectModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to reject enrollment.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

// Assign Section Modal
const openAssignSectionModal = (enrollment) => {
    enrollmentToAssign.value = enrollment;
    selectedSectionId.value = "";
    showAssignSectionModal.value = true;
};

const closeAssignSectionModal = () => {
    showAssignSectionModal.value = false;
    enrollmentToAssign.value = null;
    selectedSectionId.value = "";
};

const assignSection = () => {
    if (!enrollmentToAssign.value || !selectedSectionId.value) return;
    isSubmitting.value = true;

    router.post(
        `/admin/enrollments/${enrollmentToAssign.value.id}/assign-section`,
        {
            section_id: selectedSectionId.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Section assigned successfully!");
                closeAssignSectionModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to assign section.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openTransferSectionModal = (enrollment) => {
    enrollmentToTransfer.value = enrollment;
    selectedTransferSectionId.value = "";
    confirmTransfer.value = false;
    showTransferSectionModal.value = true;
};

const closeTransferSectionModal = () => {
    showTransferSectionModal.value = false;
    enrollmentToTransfer.value = null;
    selectedTransferSectionId.value = "";
    confirmTransfer.value = false;
};

const transferSection = () => {
    if (
        !enrollmentToTransfer.value ||
        !selectedTransferSectionId.value ||
        !confirmTransfer.value
    ) {
        return;
    }
    isSubmitting.value = true;

    router.post(
        `/admin/enrollments/${enrollmentToTransfer.value.id}/transfer-section`,
        {
            section_id: selectedTransferSectionId.value,
            confirm_transfer: true,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Student transferred to the new section.");
                closeTransferSectionModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to transfer section.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

// Enroll Student Modal
const openEnrollModal = (enrollment) => {
    enrollmentToEnroll.value = enrollment;
    showEnrollModal.value = true;
};

const closeEnrollModal = () => {
    showEnrollModal.value = false;
    enrollmentToEnroll.value = null;
};

const enrollStudent = () => {
    if (!enrollmentToEnroll.value) return;
    isSubmitting.value = true;

    router.post(
        `/admin/enrollments/${enrollmentToEnroll.value.id}/enroll`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success("Student enrolled successfully!");
                closeEnrollModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to enroll student.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openBulkApproveModal = () => {
    if (selectedIds.value.length === 0) return;
    if (selectedApprovableCount.value === 0) {
        toast.error(
            "None of the selected students are pending. Only pending enrollments can be approved.",
        );
        return;
    }
    showBulkApproveModal.value = true;
};

const closeBulkApproveModal = () => {
    showBulkApproveModal.value = false;
};

const approveSelectedStudents = () => {
    if (selectedApprovableCount.value === 0) return;
    isSubmitting.value = true;

    router.post(
        "/admin/enrollments/approve-selected",
        {
            enrollment_ids: selectedIds.value.map((id) => Number(id)),
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                toast.success(
                    page.props.flash?.success ||
                        "Selected students approved successfully!",
                );
                closeBulkApproveModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(
                    firstError || "Failed to approve selected students.",
                );
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openBulkEnrollModal = () => {
    if (selectedIds.value.length === 0) return;
    if (selectedEnrollableCount.value === 0) {
        toast.error(
            "None of the selected students are ready to enroll. Approve them first.",
        );
        return;
    }
    showBulkEnrollModal.value = true;
};

const closeBulkEnrollModal = () => {
    showBulkEnrollModal.value = false;
};

const enrollSelectedStudents = () => {
    if (selectedEnrollableCount.value === 0) return;
    isSubmitting.value = true;

    router.post(
        "/admin/enrollments/enroll-selected",
        {
            enrollment_ids: selectedIds.value.map((id) => Number(id)),
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                toast.success(
                    page.props.flash?.success ||
                        "Selected students enrolled successfully!",
                );
                selectedIds.value = [];
                closeBulkEnrollModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to enroll selected students.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};

const openBulkAssignModal = () => {
    bulkAssignMethod.value = "gwa";
    bulkYearLevelId.value = yearLevelFilter.value || "all";
    showBulkAssignModal.value = true;
};

const closeBulkAssignModal = () => {
    showBulkAssignModal.value = false;
};

const submitBulkAssign = () => {
    if (bulkAssignCount.value === 0) return;
    isSubmitting.value = true;

    const payload = {
        method: bulkAssignMethod.value,
        gwa_source: useTeacherGrades.value ? "teacher_grades" : "admission",
    };

    if (bulkYearLevelId.value !== "all") {
        payload.year_level_id = bulkYearLevelId.value;
    }

    router.post("/admin/enrollments/auto-assign-sections", payload, {
        preserveScroll: true,
        onSuccess: (page) => {
            toast.success(
                page.props.flash?.success || "Sections assigned successfully!",
            );
            closeBulkAssignModal();
        },
        onError: (errors) => {
            const firstError = Object.values(errors)[0];
            toast.error(firstError || "Failed to assign sections.");
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const openReshuffleModal = () => {
    reshuffleMethod.value = "gwa";
    reshuffleYearLevelId.value =
        yearLevelFilter.value !== "all"
            ? yearLevelFilter.value
            : props.yearLevels[0]?.id || "";
    showReshuffleModal.value = true;
};

const closeReshuffleModal = () => {
    showReshuffleModal.value = false;
    showReshuffleConfirm.value = false;
};

const submitReshuffle = () => {
    if (!reshuffleYearLevelId.value || reshuffleCount.value === 0) return;
    showReshuffleConfirm.value = true;
};

const confirmReshuffle = () => {
    isSubmitting.value = true;
    router.post(
        "/admin/enrollments/reshuffle-by-grades",
        {
            method: reshuffleMethod.value,
            year_level_id: reshuffleYearLevelId.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                toast.success(
                    page.props.flash?.success ||
                        "Sections reshuffled from teacher grades.",
                );
                closeReshuffleModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to reshuffle sections.");
            },
            onFinish: () => {
                isSubmitting.value = false;
                showReshuffleConfirm.value = false;
            },
        },
    );
};

const openDropModal = (enrollment) => {
    enrollmentToDrop.value = enrollment;
    dropRemarks.value = "";
    showDropModal.value = true;
};

const closeDropModal = () => {
    showDropModal.value = false;
    enrollmentToDrop.value = null;
    dropRemarks.value = "";
};

const submitDrop = () => {
    if (!enrollmentToDrop.value) return;
    isSubmitting.value = true;
    router.post(
        `/admin/enrollments/${enrollmentToDrop.value.id}/drop`,
        { remarks: dropRemarks.value },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                toast.success(
                    page.props.flash?.success || "Student marked as drop-out.",
                );
                closeDropModal();
            },
            onError: (errors) => {
                const firstError = Object.values(errors)[0];
                toast.error(firstError || "Failed to mark drop-out.");
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        },
    );
};
</script>

<style scoped>
@import "@/Styles/admin-common.css";

.text-muted {
    color: #555;
    font-style: italic;
}

.reject-form,
.assign-section-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.reject-info,
.assign-info {
    text-align: left;
}

.reject-info p,
.assign-info p {
    margin: 0 0 1rem 0;
    color: #333;
}

.bulk-assign-intro,
.bulk-assign-count {
    margin: 0;
    color: #333;
    font-size: 0.9rem;
    line-height: 1.45;
}

.bulk-assign-count {
    font-weight: 600;
    color: #003366;
}

.method-options {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.method-option {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.7rem 0.75rem;
    border: 1px solid #c5c5c5;
    background: #fff;
    cursor: pointer;
}

.method-option.selected {
    border-color: #003366;
    background: #f3f6f9;
}

.method-option input {
    margin-top: 0.2rem;
    accent-color: #003366;
}

.method-option span {
    color: #333;
    font-size: 0.85rem;
    line-height: 1.4;
}

.method-option strong {
    display: block;
    color: #003366;
    margin-bottom: 0.15rem;
    font-size: 0.9rem;
}

.col-check {
    width: 2.25rem;
    text-align: center;
}

.col-check input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #003366;
}

.selection-count {
    font-weight: 600;
}

.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.page-btn {
    min-width: 2rem;
    height: 2rem;
    padding: 0 0.45rem;
    border: 1px solid #003366;
    background: #fff;
    color: #003366;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.page-btn.active {
    background: #003366;
    color: #fff;
}

.page-btn:hover:not(:disabled):not(.active) {
    background: #e8eef4;
}

.page-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.modal-body textarea {
    width: 100%;
    margin-top: 0.4rem;
    border: 1px solid #bdbdbd;
    padding: 0.45rem 0.55rem;
}

.transfer-note {
    padding: 0.85rem 0.95rem;
    border: 1px solid #c9a227;
    background: #fff8e6;
    color: #5c4a12;
}

.transfer-note p {
    margin: 0 0 0.65rem;
    font-size: 0.9rem;
    line-height: 1.45;
}

.transfer-note p:last-child {
    margin-bottom: 0;
}

.transfer-confirm {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    font-size: 0.9rem;
    color: #333;
    cursor: pointer;
}

.transfer-confirm input {
    margin-top: 0.2rem;
    accent-color: #003366;
}

.btn-icon.transfer:hover {
    background: #003366;
    color: #fff;
}
</style>
