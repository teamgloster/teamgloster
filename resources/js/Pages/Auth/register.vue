<template>
    <Head :title="pageTitle + ' - TNHS'" />
    <div class="auth-container">
        <div class="overlay"></div>
        <div class="auth-content">
            <div class="auth-card-wrapper">
                <div class="auth-card">
                    <div class="card-bar">{{ pageTitle }}</div>
                    <div class="card-body">
                    <div class="auth-header">
                        <img :src="logo" alt="TNHS Logo" class="logo" />
                        <p class="info-text">Tambo National High School</p>
                        <p class="info-text-small">Buhi, Camarines Sur</p>
                        <p class="subtitle">{{ pageSubtitle }}</p>
                    </div>

                    <div class="step-indicator">
                        <div
                            class="step"
                            :class="{
                                active: currentStep === 1,
                                completed: currentStep > 1,
                            }"
                        >
                            <div class="step-number">1</div>
                            <div class="step-label">Personal Info</div>
                        </div>
                        <div
                            class="step-line"
                            :class="{ active: currentStep > 1 }"
                        ></div>
                        <div
                            class="step"
                            :class="{
                                active: currentStep === 2,
                                completed: currentStep > 2,
                            }"
                        >
                            <div class="step-number">2</div>
                            <div class="step-label">Documents</div>
                        </div>
                        <div
                            class="step-line"
                            :class="{ active: currentStep > 2 }"
                        ></div>
                        <div
                            class="step"
                            :class="{
                                active: currentStep === 3,
                                completed: currentStep > 3,
                            }"
                        >
                            <div class="step-number">3</div>
                            <div class="step-label">Review</div>
                        </div>
                    </div>
                    <p class="step-status">Step {{ currentStep }} of 3</p>

                    <form @submit.prevent="handleSubmit" class="auth-form">
                        <!-- Step 1: Personal Information -->
                        <div v-if="currentStep === 1" class="step-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input
                                        id="first_name"
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        placeholder="Juan"
                                        class="form-input"
                                        :class="{
                                            'input-error': errors.first_name,
                                        }"
                                    />
                                    <span
                                        v-if="errors.first_name"
                                        class="error-message"
                                        >{{ errors.first_name }}</span
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="middle_name">Middle Name</label>
                                    <input
                                        id="middle_name"
                                        v-model="form.middle_name"
                                        type="text"
                                        placeholder="Santos"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input
                                        id="last_name"
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        placeholder="Dela Cruz"
                                        class="form-input"
                                        :class="{
                                            'input-error': errors.last_name,
                                        }"
                                    />
                                    <span
                                        v-if="errors.last_name"
                                        class="error-message"
                                        >{{ errors.last_name }}</span
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="suffix">Suffix</label>
                                    <input
                                        id="suffix"
                                        v-model="form.suffix"
                                        type="text"
                                        placeholder="Jr., Sr., III"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        placeholder="juan@example.com"
                                        class="form-input"
                                        :class="{ 'input-error': errors.email }"
                                    />
                                    <span
                                        v-if="errors.email"
                                        class="error-message"
                                        >{{ errors.email }}</span
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="phone_no">Phone No.</label>
                                    <input
                                        id="phone_no"
                                        v-model="form.phone_no"
                                        type="text"
                                        placeholder="+639123456789"
                                        class="form-input"
                                        :class="{
                                            'input-error': errors.phone_no,
                                        }"
                                    />
                                    <span
                                        v-if="errors.phone_no"
                                        class="error-message"
                                        >{{ errors.phone_no }}</span
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date_of_birth"
                                        >Date of Birth</label
                                    >
                                    <input
                                        id="date_of_birth"
                                        v-model="form.date_of_birth"
                                        type="date"
                                        required
                                        class="form-input"
                                        :class="{
                                            'input-error': errors.date_of_birth,
                                        }"
                                    />
                                    <span
                                        v-if="errors.date_of_birth"
                                        class="error-message"
                                        >{{ errors.date_of_birth }}</span
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Gender</label>
                                    <div class="gender-group">
                                        <label class="radio-label">
                                            <input
                                                v-model="form.gender"
                                                type="radio"
                                                value="Male"
                                                required
                                            />
                                            <span>Male</span>
                                        </label>
                                        <label class="radio-label">
                                            <input
                                                v-model="form.gender"
                                                type="radio"
                                                value="Female"
                                                required
                                            />
                                            <span>Female</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="lrn"
                                        >LRN (Learner Reference Number)</label
                                    >
                                    <input
                                        id="lrn"
                                        v-model="form.lrn"
                                        type="text"
                                        required
                                        placeholder="123456789012"
                                        class="form-input"
                                        :class="{ 'input-error': errors.lrn }"
                                        maxlength="12"
                                        pattern="[0-9]{12}"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        @input="onLrnInput"
                                        @keydown="onLrnKeydown"
                                    />
                                    <span class="field-hint"
                                        >Must be a unique 12-digit LRN.</span
                                    >
                                    <span
                                        v-if="errors.lrn"
                                        class="error-message"
                                        >{{ errors.lrn }}</span
                                    >
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="province">Province</label>
                                    <select
                                        id="province"
                                        v-model="form.province"
                                        class="form-input"
                                        required
                                        @change="onProvinceChange"
                                        :disabled="loadingProvinces"
                                    >
                                        <option value="">
                                            {{
                                                loadingProvinces
                                                    ? "Loading..."
                                                    : "Select Province"
                                            }}
                                        </option>
                                        <option
                                            v-for="province in provinces"
                                            :key="province.code"
                                            :value="province.name"
                                            :data-code="province.code"
                                        >
                                            {{ province.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="municipality"
                                        >Municipality/City</label
                                    >
                                    <select
                                        id="municipality"
                                        v-model="form.municipality"
                                        class="form-input"
                                        required
                                        @change="onMunicipalityChange"
                                        :disabled="
                                            !form.province ||
                                            loadingMunicipalities
                                        "
                                    >
                                        <option value="">
                                            {{
                                                loadingMunicipalities
                                                    ? "Loading..."
                                                    : "Select Municipality/City"
                                            }}
                                        </option>
                                        <option
                                            v-for="municipality in municipalities"
                                            :key="municipality.code"
                                            :value="municipality.name"
                                            :data-code="municipality.code"
                                        >
                                            {{ municipality.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="barangay">Barangay</label>
                                    <select
                                        id="barangay"
                                        v-model="form.barangay"
                                        class="form-input"
                                        required
                                        :disabled="
                                            !form.municipality ||
                                            loadingBarangays
                                        "
                                    >
                                        <option value="">
                                            {{
                                                loadingBarangays
                                                    ? "Loading..."
                                                    : "Select Barangay"
                                            }}
                                        </option>
                                        <option
                                            v-for="barangay in barangays"
                                            :key="barangay.code"
                                            :value="barangay.name"
                                        >
                                            {{ barangay.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="guardian_full_name"
                                        >Guardian Full Name</label
                                    >
                                    <input
                                        id="guardian_full_name"
                                        v-model="form.guardian_full_name"
                                        type="text"
                                        placeholder="Guardian's name"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="guardian_contact_no"
                                        >Guardian Contact No.</label
                                    >
                                    <input
                                        id="guardian_contact_no"
                                        v-model="form.guardian_contact_no"
                                        type="text"
                                        placeholder="+639123456789"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="guardian_relationship"
                                        >Relationship</label
                                    >
                                    <input
                                        id="guardian_relationship"
                                        v-model="form.guardian_relationship"
                                        type="text"
                                        placeholder="e.g., Aunt, Uncle, Elder Sibling"
                                        class="form-input"
                                    />
                                </div>
                            </div>

                            <!-- Family Background Section -->
                            <div class="section-divider">
                                <h3 class="section-title">Family Background</h3>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="father_name"
                                        >Father's Name</label
                                    >
                                    <input
                                        id="father_name"
                                        v-model="form.father_name"
                                        type="text"
                                        placeholder="Father's full name"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="father_occupation"
                                        >Father's Occupation</label
                                    >
                                    <input
                                        id="father_occupation"
                                        v-model="form.father_occupation"
                                        type="text"
                                        placeholder="Occupation"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="father_contact"
                                        >Father's Contact</label
                                    >
                                    <input
                                        id="father_contact"
                                        v-model="form.father_contact"
                                        type="text"
                                        placeholder="+639123456789"
                                        class="form-input"
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="mother_name"
                                        >Mother's Name</label
                                    >
                                    <input
                                        id="mother_name"
                                        v-model="form.mother_name"
                                        type="text"
                                        placeholder="Mother's full name"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="mother_occupation"
                                        >Mother's Occupation</label
                                    >
                                    <input
                                        id="mother_occupation"
                                        v-model="form.mother_occupation"
                                        type="text"
                                        placeholder="Occupation"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="mother_contact"
                                        >Mother's Contact</label
                                    >
                                    <input
                                        id="mother_contact"
                                        v-model="form.mother_contact"
                                        type="text"
                                        placeholder="+639123456789"
                                        class="form-input"
                                    />
                                </div>
                            </div>

                            <!-- Medical Information Section -->
                            <div class="section-divider">
                                <h3 class="section-title">
                                    Medical Information
                                </h3>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="blood_type">Blood Type</label>
                                    <select
                                        id="blood_type"
                                        v-model="form.blood_type"
                                        class="form-input"
                                    >
                                        <option value="">
                                            Select Blood Type
                                        </option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="allergies">Allergies</label>
                                    <input
                                        id="allergies"
                                        v-model="form.allergies"
                                        type="text"
                                        placeholder="List any allergies (if none, type 'None')"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="medical_conditions"
                                        >Medical Conditions</label
                                    >
                                    <input
                                        id="medical_conditions"
                                        v-model="form.medical_conditions"
                                        type="text"
                                        placeholder="List any medical conditions (if none, type 'None')"
                                        class="form-input"
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="emergency_contact_person"
                                        >Emergency Contact Person</label
                                    >
                                    <input
                                        id="emergency_contact_person"
                                        v-model="form.emergency_contact_person"
                                        type="text"
                                        placeholder="Name of emergency contact"
                                        class="form-input"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="emergency_contact_number"
                                        >Emergency Contact Number</label
                                    >
                                    <input
                                        id="emergency_contact_number"
                                        v-model="form.emergency_contact_number"
                                        type="text"
                                        placeholder="+639123456789"
                                        class="form-input"
                                    />
                                </div>
                                <div class="form-group"></div>
                            </div>

                            <!-- Admission Data Section -->
                            <div class="section-divider">
                                <h3 class="section-title">
                                    Admission Information
                                </h3>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="year_level_applying"
                                        >Year Level Applying For</label
                                    >
                                    <select
                                        id="year_level_applying"
                                        v-model="form.year_level_applying"
                                        class="form-input"
                                        required
                                    >
                                        <option value="">
                                            Select Year Level
                                        </option>
                                        <option value="Grade 7">Grade 7</option>
                                        <option value="Grade 8">Grade 8</option>
                                        <option value="Grade 9">Grade 9</option>
                                        <option value="Grade 10">
                                            Grade 10
                                        </option>
                                        <option value="Grade 11">
                                            Grade 11
                                        </option>
                                        <option value="Grade 12">
                                            Grade 12
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="preferred_strand"
                                        >Preferred Strand (for Grade
                                        11-12)</label
                                    >
                                    <select
                                        id="preferred_strand"
                                        v-model="form.preferred_strand"
                                        class="form-input"
                                        :disabled="!isSeniorHigh"
                                    >
                                        <option value="">Select Strand</option>
                                        <option value="STEM">STEM</option>
                                        <option value="HUMSS">HUMSS</option>
                                        <option value="ABM">ABM</option>
                                        <option value="GAS">GAS</option>
                                        <option value="TVL">TVL</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="school_year_applying"
                                        >School Year</label
                                    >
                                    <select
                                        v-if="currentSchoolYear"
                                        id="school_year_applying"
                                        v-model="form.school_year_applying"
                                        class="form-input"
                                        required
                                    >
                                        <option value="">
                                            Select school year
                                        </option>
                                        <option
                                            v-for="year in schoolYearOptions"
                                            :key="year"
                                            :value="year"
                                        >
                                            {{ year }}
                                        </option>
                                    </select>
                                    <input
                                        v-else
                                        id="school_year_applying"
                                        v-model="form.school_year_applying"
                                        type="text"
                                        placeholder="e.g., 2026-2027"
                                        class="form-input"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="previous_school"
                                        >Previous School</label
                                    >
                                    <input
                                        id="previous_school"
                                        v-model="form.previous_school"
                                        type="text"
                                        placeholder="Name of previous school"
                                        class="form-input"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="year_graduated"
                                        >Year Graduated</label
                                    >
                                    <input
                                        id="year_graduated"
                                        v-model="form.year_graduated"
                                        type="number"
                                        min="1990"
                                        :max="maxGraduationYear"
                                        placeholder="e.g., 2025"
                                        class="form-input"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="previous_gwa"
                                        >Previous GWA</label
                                    >
                                    <input
                                        id="previous_gwa"
                                        v-model="form.previous_gwa"
                                        type="text"
                                        placeholder="e.g., 90.5"
                                        class="form-input"
                                    />
                                </div>
                            </div>

                            <!-- Password Section -->
                            <div class="section-divider">
                                <h3 class="section-title">Account Security</h3>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="password-input-wrapper">
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            :type="
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            required
                                            placeholder="Enter password"
                                            class="form-input password-input"
                                            :class="{
                                                'input-error': errors.password,
                                            }"
                                        />
                                        <button
                                            type="button"
                                            class="password-toggle"
                                            @click="
                                                showPassword = !showPassword
                                            "
                                            :aria-label="
                                                showPassword
                                                    ? 'Hide password'
                                                    : 'Show password'
                                            "
                                            :title="
                                                showPassword
                                                    ? 'Hide password'
                                                    : 'Show password'
                                            "
                                        >
                                            <EyeOff
                                                v-if="showPassword"
                                                :size="18"
                                            />
                                            <Eye v-else :size="18" />
                                        </button>
                                    </div>
                                    <span
                                        v-if="errors.password"
                                        class="error-message"
                                        >{{ errors.password }}</span
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation"
                                        >Confirm Password</label
                                    >
                                    <div class="password-input-wrapper">
                                        <input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            :type="
                                                showConfirmPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            required
                                            placeholder="Confirm password"
                                            class="form-input password-input"
                                        />
                                        <button
                                            type="button"
                                            class="password-toggle"
                                            @click="
                                                showConfirmPassword =
                                                    !showConfirmPassword
                                            "
                                            :aria-label="
                                                showConfirmPassword
                                                    ? 'Hide confirm password'
                                                    : 'Show confirm password'
                                            "
                                            :title="
                                                showConfirmPassword
                                                    ? 'Hide confirm password'
                                                    : 'Show confirm password'
                                            "
                                        >
                                            <EyeOff
                                                v-if="showConfirmPassword"
                                                :size="18"
                                            />
                                            <Eye v-else :size="18" />
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group"></div>
                            </div>

                            <div class="step-navigation">
                                <button
                                    type="button"
                                    class="btn-next"
                                    @click="nextStep"
                                >
                                    Next: Upload Documents
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Document Upload -->
                        <div v-if="currentStep === 2" class="step-content">
                            <div class="section-divider">
                                <h3 class="section-title">
                                    Required Documents
                                </h3>
                                <p class="section-description">
                                    {{ documentsStepDescription }}
                                </p>
                            </div>

                            <div class="documents-table-wrap">
                                <table class="documents-table">
                                    <thead>
                                        <tr>
                                            <th>Document</th>
                                            <th>Accepted Format</th>
                                            <th>File</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="doc in requiredAdmissionDocuments"
                                            :key="doc.key"
                                        >
                                            <td>{{ doc.label }}</td>
                                            <td>{{ doc.format }}</td>
                                            <td>
                                                <input
                                                    type="file"
                                                    :id="doc.key + '_file'"
                                                    class="file-input"
                                                    :accept="doc.accept"
                                                    @change="
                                                        handleFileUpload(
                                                            $event,
                                                            doc.key,
                                                        )
                                                    "
                                                />
                                                <label
                                                    :for="doc.key + '_file'"
                                                    class="file-label"
                                                >
                                                    Choose File
                                                </label>
                                                <span
                                                    v-if="
                                                        documents[doc.key].name
                                                    "
                                                    class="file-name"
                                                    >{{
                                                        documents[doc.key].name
                                                    }}</span
                                                >
                                            </td>
                                            <td>
                                                {{
                                                    documents[doc.key].name
                                                        ? "Uploaded"
                                                        : "Not uploaded"
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="step-navigation">
                                <button
                                    type="button"
                                    class="btn-secondary"
                                    @click="previousStep"
                                >
                                    Back
                                </button>
                                <button
                                    type="button"
                                    class="btn-next"
                                    @click="nextStep"
                                >
                                    {{
                                        allDocumentsUploaded
                                            ? "Next: Review"
                                            : "Skip & Review"
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Review and Submit -->
                        <div v-if="currentStep === 3" class="step-content">
                            <div class="section-divider">
                                <h3 class="section-title">
                                    Review Your Application
                                </h3>
                                <p class="section-description">
                                    Please review your information before
                                    submitting
                                </p>
                            </div>

                            <div class="review-section">
                                <div class="review-card">
                                    <h4>Personal Information</h4>
                                    <div class="review-grid">
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Full Name:</span
                                            >
                                            <span class="review-value"
                                                >{{ form.first_name }}
                                                {{ form.middle_name }}
                                                {{ form.last_name }}
                                                {{ form.suffix }}</span
                                            >
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Email:</span
                                            >
                                            <span class="review-value">{{
                                                form.email
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Phone:</span
                                            >
                                            <span class="review-value">{{
                                                form.phone_no
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Date of Birth:</span
                                            >
                                            <span class="review-value">{{
                                                form.date_of_birth
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Gender:</span
                                            >
                                            <span class="review-value">{{
                                                form.gender
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Address:</span
                                            >
                                            <span class="review-value"
                                                >{{ form.barangay }},
                                                {{ form.municipality }},
                                                {{ form.province }}</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="review-card">
                                    <h4>Admission Details</h4>
                                    <div class="review-grid">
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Year Level:</span
                                            >
                                            <span class="review-value">{{
                                                form.year_level_applying
                                            }}</span>
                                        </div>
                                        <div
                                            class="review-item"
                                            v-if="form.preferred_strand"
                                        >
                                            <span class="review-label"
                                                >Preferred Strand:</span
                                            >
                                            <span class="review-value">{{
                                                form.preferred_strand
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >School Year:</span
                                            >
                                            <span class="review-value">{{
                                                form.school_year_applying
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Previous School:</span
                                            >
                                            <span class="review-value">{{
                                                form.previous_school
                                            }}</span>
                                        </div>
                                        <div class="review-item">
                                            <span class="review-label"
                                                >Year Graduated:</span
                                            >
                                            <span class="review-value">{{
                                                form.year_graduated
                                            }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="review-card">
                                    <h4>Uploaded Documents</h4>
                                    <div class="review-documents">
                                        <div
                                            v-for="doc in requiredAdmissionDocuments"
                                            v-show="documents[doc.key].name"
                                            :key="'review-' + doc.key"
                                            class="doc-review-item"
                                        >
                                            <CheckCircle
                                                :size="18"
                                                class="doc-check"
                                            />
                                            <span
                                                >{{ doc.short }}:
                                                {{
                                                    documents[doc.key].name
                                                }}</span
                                            >
                                        </div>
                                        <p
                                            v-if="
                                                !requiredAdmissionDocuments.some(
                                                    (doc) =>
                                                        documents[doc.key]
                                                            .name,
                                                )
                                            "
                                            class="section-description"
                                        >
                                            No documents uploaded yet.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="step-navigation">
                                <button
                                    type="button"
                                    class="btn-secondary"
                                    @click="previousStep"
                                >
                                    Back
                                </button>
                                <button
                                    type="button"
                                    class="btn-download"
                                    @click="downloadAsPDF"
                                >
                                    <Download :size="18" />
                                    Download as PDF
                                </button>
                                <button
                                    type="submit"
                                    class="submit-btn"
                                    :disabled="processing"
                                >
                                    {{
                                        processing
                                            ? "Submitting..."
                                            : "Submit Application"
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="auth-footer">
                        <p>
                            Already have an account?
                            <a href="/login/student" class="link">Login here</a>
                        </p>
                        <a href="/" class="link">Back to Home</a>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, markRaw } from "vue";
import { router, Head, usePage } from "@inertiajs/vue3";
import { useToast } from "@/composables/useNotify";
import { CheckCircle, Download, Eye, EyeOff } from "lucide-vue-next";
import { jsPDF } from "jspdf";

const toast = useToast();
const page = usePage();

const props = defineProps({
    errors: {
        type: Object,
        default: () => ({}),
    },
    currentSchoolYear: {
        type: String,
        default: "",
    },
});

const logo = "/images/311494412_220590550318716_333223840059485017_n.jpg";

// Step Management
const currentStep = ref(1);

// Document Upload State
const documents = ref({
    form_137: { file: null, name: "" },
    birth_certificate: { file: null, name: "" },
    good_moral_certificate: { file: null, name: "" },
    accomplishment_credentials: { file: null, name: "" },
});

// Keep raw File objects out of Vue reactivity so Inertia can send them as uploads.
const documentFiles = {};

const allDocumentsUploaded = computed(() => {
    return requiredAdmissionDocuments.value.every(
        (doc) => documents.value[doc.key]?.file,
    );
});

const isSeniorHigh = computed(() => {
    return (
        form.value.year_level_applying === "Grade 11" ||
        form.value.year_level_applying === "Grade 12"
    );
});

const applicantDocumentCategory = computed(() => {
    if (isSeniorHigh.value) {
        return "senior_high";
    }
    if (form.value.year_level_applying === "Grade 7") {
        return "incoming_grade_7";
    }
    return "transferee";
});

const requiredAdmissionDocuments = computed(() => {
    const common = [
        {
            key: "birth_certificate",
            label: "PSA Birth Certificate",
            short: "Birth Certificate",
            accept: "application/pdf",
            format: "PDF",
        },
    ];

    if (applicantDocumentCategory.value === "senior_high") {
        return [
            {
                key: "accomplishment_credentials",
                label: "Accomplishment Credentials",
                short: "Accomplishment Credentials",
                accept: "application/pdf",
                format: "PDF",
            },
            ...common,
        ];
    }

    return [
        {
            key: "form_137",
            label: "Form 137 (Permanent Record)",
            short: "Form 137",
            accept: "application/pdf",
            format: "PDF",
        },
        {
            key: "good_moral_certificate",
            label: "Good Moral Certificate",
            short: "Good Moral",
            accept: "application/pdf",
            format: "PDF",
        },
        ...common,
    ];
});

const documentsStepDescription = computed(() => {
    if (!form.value.year_level_applying) {
        return "Select a year level in Step 1 to see the documents you need. You may skip this step and upload later.";
    }
    if (applicantDocumentCategory.value === "senior_high") {
        return "Senior High School applicants (Grade 11-12) need accomplishment credentials and a PSA birth certificate. You may skip this step and upload later.";
    }
    if (applicantDocumentCategory.value === "incoming_grade_7") {
        return "Incoming Grade 7 applicants need Form 137, a Good Moral Certificate, and a PSA birth certificate. You may skip this step and upload later.";
    }
    return "Transferees need Form 137, a Good Moral Certificate, and a PSA birth certificate. You may skip this step and upload later.";
});

const isAdmissionRoute = computed(() => {
    return window.location.pathname.includes("/admission");
});

const pageTitle = computed(() => {
    return isAdmissionRoute.value
        ? "Admission Application"
        : "Student Registration";
});

const pageSubtitle = computed(() => {
    return isAdmissionRoute.value
        ? "Complete this form to apply for admission."
        : "Complete this form to create a student account.";
});

const schoolYearOptions = computed(() => {
    if (!props.currentSchoolYear) {
        return [];
    }
    return [props.currentSchoolYear];
});

// Philippine address data
const provinces = ref([]);
const municipalities = ref([]);
const barangays = ref([]);
const loadingProvinces = ref(false);
const loadingMunicipalities = ref(false);
const loadingBarangays = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const maxGraduationYear = new Date().getFullYear() + 1;

const form = ref({
    first_name: "",
    middle_name: "",
    last_name: "",
    suffix: "",
    email: "",
    phone_no: "",
    date_of_birth: "",
    gender: "",
    province: "",
    province_code: "",
    municipality: "",
    municipality_code: "",
    barangay: "",
    lrn: "",
    previous_gwa: "",
    guardian_full_name: "",
    guardian_contact_no: "",
    guardian_relationship: "",
    // Family Background
    father_name: "",
    father_occupation: "",
    father_contact: "",
    mother_name: "",
    mother_occupation: "",
    mother_contact: "",
    // Medical Information
    blood_type: "",
    allergies: "",
    medical_conditions: "",
    emergency_contact_person: "",
    emergency_contact_number: "",
    // Admission Data
    year_level_applying: "",
    preferred_strand: "",
    previous_school: "",
    year_graduated: "",
    school_year_applying: props.currentSchoolYear || "",
    password: "",
    password_confirmation: "",
    role: "student",
});

const processing = ref(false);

// Fetch provinces on mount and check flash messages
onMounted(async () => {
    const flash = page.props.flash;
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }

    await fetchProvinces();
});

const fetchProvinces = async () => {
    loadingProvinces.value = true;
    try {
        const response = await fetch("https://psgc.gitlab.io/api/provinces/");
        const data = await response.json();
        provinces.value = data.sort((a, b) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error("Error fetching provinces:", error);
    } finally {
        loadingProvinces.value = false;
    }
};

const fetchMunicipalities = async (provinceCode) => {
    loadingMunicipalities.value = true;
    municipalities.value = [];
    barangays.value = [];
    form.value.municipality = "";
    form.value.municipality_code = "";
    form.value.barangay = "";

    try {
        const response = await fetch(
            `https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`,
        );
        const data = await response.json();
        municipalities.value = data.sort((a, b) =>
            a.name.localeCompare(b.name),
        );
    } catch (error) {
        console.error("Error fetching municipalities:", error);
    } finally {
        loadingMunicipalities.value = false;
    }
};

const fetchBarangays = async (municipalityCode) => {
    loadingBarangays.value = true;
    barangays.value = [];
    form.value.barangay = "";

    try {
        const response = await fetch(
            `https://psgc.gitlab.io/api/cities-municipalities/${municipalityCode}/barangays/`,
        );
        const data = await response.json();
        barangays.value = data.sort((a, b) => a.name.localeCompare(b.name));
    } catch (error) {
        console.error("Error fetching barangays:", error);
    } finally {
        loadingBarangays.value = false;
    }
};

const onProvinceChange = (event) => {
    const selectedOption = event.target.selectedOptions[0];
    if (selectedOption && selectedOption.dataset.code) {
        form.value.province_code = selectedOption.dataset.code;
        fetchMunicipalities(selectedOption.dataset.code);
    }
};

const onMunicipalityChange = (event) => {
    const selectedOption = event.target.selectedOptions[0];
    if (selectedOption && selectedOption.dataset.code) {
        form.value.municipality_code = selectedOption.dataset.code;
        fetchBarangays(selectedOption.dataset.code);
    }
};

// Watch year level changes to clear strand if not senior high
watch(
    () => form.value.year_level_applying,
    (newValue) => {
        if (newValue !== "Grade 11" && newValue !== "Grade 12") {
            form.value.preferred_strand = "";
        }

        const allowed = new Set(
            requiredAdmissionDocuments.value.map((doc) => doc.key),
        );
        Object.keys(documents.value).forEach((key) => {
            if (!allowed.has(key)) {
                delete documentFiles[key];
                documents.value[key] = { file: null, name: "" };
            }
        });
    },
);

// Step Navigation Functions
const onLrnInput = (event) => {
    const digits = event.target.value.replace(/\D/g, "").slice(0, 12);
    form.value.lrn = digits;
    event.target.value = digits;
};

const onLrnKeydown = (event) => {
    const allowedKeys = [
        "Backspace",
        "Delete",
        "Tab",
        "Escape",
        "Enter",
        "ArrowLeft",
        "ArrowRight",
        "Home",
        "End",
    ];

    if (allowedKeys.includes(event.key) || event.ctrlKey || event.metaKey) {
        return;
    }

    if (!/^\d$/.test(event.key)) {
        event.preventDefault();
    }
};

const nextStep = () => {
    if (currentStep.value < 3) {
        // Validate current step before proceeding
        if (currentStep.value === 1) {
            // Basic validation for step 1
            if (
                !form.value.first_name ||
                !form.value.last_name ||
                !form.value.email ||
                !form.value.password ||
                !form.value.year_level_applying ||
                !/^\d{12}$/.test(form.value.lrn)
            ) {
                toast.error(
                    "Please fill in all required fields, including a unique 12-digit LRN and year level.",
                );
                return;
            }
        }
        currentStep.value++;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

const previousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
        window.scrollTo({ top: 0, behavior: "smooth" });
    }
};

// File Upload Handler
const handleFileUpload = (event, documentType) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type !== "application/pdf") {
            toast.error("Please upload a PDF file.");
            event.target.value = "";
            return;
        }

        // Validate file size (max 5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            toast.error("File size must be less than 5MB");
            event.target.value = "";
            return;
        }

        documentFiles[documentType] = file;
        documents.value[documentType] = {
            file: markRaw(file),
            name: file.name,
        };
        toast.success(`${file.name} selected`);
    }
};

// Form Submission
const handleSubmit = () => {
    if (currentStep.value === 3) {
        submit();
    }
};

// PDF Download Function
const downloadAsPDF = async () => {
    const doc = new jsPDF();
    const pageWidth = doc.internal.pageSize.getWidth();
    const margin = 15;
    let yPosition = 15;

    // Helper function to add text with word wrap
    const addText = (text, x, y, maxWidth = pageWidth - 2 * margin) => {
        const lines = doc.splitTextToSize(text, maxWidth);
        doc.text(lines, x, y);
        return lines.length * 7; // Return height used
    };

    // Helper function to load and convert image to base64
    const loadImage = (url) => {
        return new Promise((resolve) => {
            const img = new window.Image();

            // Create full URL if relative path
            const fullUrl = url.startsWith("http")
                ? url
                : `${window.location.origin}${url}`;
            console.log("Loading image from:", fullUrl);

            img.onload = () => {
                try {
                    const canvas = document.createElement("canvas");
                    canvas.width = img.naturalWidth || img.width;
                    canvas.height = img.naturalHeight || img.height;
                    const ctx = canvas.getContext("2d");

                    // Draw white background first
                    ctx.fillStyle = "#FFFFFF";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    // Draw the image
                    ctx.drawImage(img, 0, 0);

                    // Convert to JPEG format
                    const dataUrl = canvas.toDataURL("image/jpeg", 1.0);
                    console.log(
                        "Image converted successfully, length:",
                        dataUrl.length,
                    );
                    resolve(dataUrl);
                } catch (err) {
                    console.error("Error converting image:", err);
                    resolve(null);
                }
            };

            img.onerror = (err) => {
                console.error("Error loading image:", err);
                // Try without crossOrigin as fallback
                const imgFallback = new window.Image();
                imgFallback.onload = () => {
                    try {
                        const canvas = document.createElement("canvas");
                        canvas.width =
                            imgFallback.naturalWidth || imgFallback.width;
                        canvas.height =
                            imgFallback.naturalHeight || imgFallback.height;
                        const ctx = canvas.getContext("2d");
                        ctx.fillStyle = "#FFFFFF";
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(imgFallback, 0, 0);
                        const dataUrl = canvas.toDataURL("image/jpeg", 1.0);
                        console.log(
                            "Image loaded via fallback, length:",
                            dataUrl.length,
                        );
                        resolve(dataUrl);
                    } catch (err2) {
                        console.error("Fallback also failed:", err2);
                        resolve(null);
                    }
                };
                imgFallback.onerror = () => {
                    console.error("Fallback load failed for:", fullUrl);
                    resolve(null);
                };
                imgFallback.src = fullUrl;
            };

            // Try with crossOrigin first for CORS-enabled servers
            img.crossOrigin = "anonymous";
            img.src = fullUrl;
        });
    };

    // Load and add logo
    let logoLoaded = false;
    try {
        console.log("Attempting to load logo:", logo);
        const logoBase64 = await loadImage(logo);

        if (logoBase64 && logoBase64.length > 100) {
            console.log("Logo loaded successfully, adding to PDF");
            // Add logo (centered at top)
            const logoSize = 30;
            const logoX = (pageWidth - logoSize) / 2;

            // Add the logo image
            doc.addImage(
                logoBase64,
                "JPEG",
                logoX,
                yPosition,
                logoSize,
                logoSize,
                undefined,
                "FAST",
            );

            yPosition += logoSize + 5;
            logoLoaded = true;
        } else {
            console.warn(
                "Logo could not be loaded - logoBase64 is",
                logoBase64 ? "too short" : "null",
            );
            yPosition += 5;
        }
    } catch (error) {
        console.error("Error in logo loading process:", error);
        yPosition += 5;
    }

    // Header with school information
    doc.setFontSize(18);
    doc.setFont(undefined, "bold");
    doc.setTextColor(0, 51, 102); // TNHS blue color
    doc.text("TAMBO NATIONAL HIGH SCHOOL", pageWidth / 2, yPosition, {
        align: "center",
    });
    yPosition += 8;

    doc.setFontSize(10);
    doc.setFont(undefined, "normal");
    doc.setTextColor(100, 100, 100);
    doc.text("Tambo, Lipa City, Batangas", pageWidth / 2, yPosition, {
        align: "center",
    });
    yPosition += 10;

    // Decorative line
    doc.setDrawColor(0, 51, 102);
    doc.setLineWidth(0.5);
    doc.line(margin, yPosition, pageWidth - margin, yPosition);
    yPosition += 8;

    // Document Title
    doc.setFontSize(16);
    doc.setFont(undefined, "bold");
    doc.setTextColor(0, 51, 102);
    doc.text(
        isAdmissionRoute.value
            ? "ADMISSION APPLICATION FORM"
            : "STUDENT REGISTRATION FORM",
        pageWidth / 2,
        yPosition,
        { align: "center" },
    );
    yPosition += 10;

    // Reset text color for content
    doc.setTextColor(0, 0, 0);

    // Personal Information
    doc.setFontSize(14);
    doc.setFont(undefined, "bold");
    doc.text("Personal Information", margin, yPosition);
    yPosition += 10;

    doc.setFontSize(10);
    doc.setFont(undefined, "normal");
    const fullName =
        `${form.value.first_name} ${form.value.middle_name || ""} ${form.value.last_name} ${form.value.suffix || ""}`.trim();
    yPosition += addText(`Full Name: ${fullName}`, margin, yPosition);
    yPosition += addText(
        `Email: ${form.value.email || "N/A"}`,
        margin,
        yPosition,
    );
    yPosition += addText(
        `Phone: ${form.value.phone_no || "N/A"}`,
        margin,
        yPosition,
    );
    yPosition += addText(
        `Date of Birth: ${form.value.date_of_birth || "N/A"}`,
        margin,
        yPosition,
    );
    yPosition += addText(
        `Gender: ${form.value.gender || "N/A"}`,
        margin,
        yPosition,
    );
    yPosition += addText(`LRN: ${form.value.lrn || "N/A"}`, margin, yPosition);
    yPosition += 5;

    // Address
    doc.setFontSize(14);
    doc.setFont(undefined, "bold");
    yPosition += addText("Address", margin, yPosition);
    yPosition += 5;

    doc.setFontSize(10);
    doc.setFont(undefined, "normal");
    const address =
        `${form.value.barangay || ""}, ${form.value.municipality || ""}, ${form.value.province || ""}`.trim();
    yPosition += addText(`Address: ${address || "N/A"}`, margin, yPosition);
    yPosition += 5;

    // Family Background
    if (
        form.value.father_name ||
        form.value.mother_name ||
        form.value.guardian_full_name
    ) {
        doc.setFontSize(14);
        doc.setFont(undefined, "bold");
        yPosition += addText("Family Background", margin, yPosition);
        yPosition += 5;

        doc.setFontSize(10);
        doc.setFont(undefined, "normal");
        if (form.value.father_name) {
            yPosition += addText(
                `Father: ${form.value.father_name}`,
                margin,
                yPosition,
            );
            if (form.value.father_occupation) {
                yPosition += addText(
                    `  Occupation: ${form.value.father_occupation}`,
                    margin,
                    yPosition,
                );
            }
            if (form.value.father_contact) {
                yPosition += addText(
                    `  Contact: ${form.value.father_contact}`,
                    margin,
                    yPosition,
                );
            }
        }
        if (form.value.mother_name) {
            yPosition += addText(
                `Mother: ${form.value.mother_name}`,
                margin,
                yPosition,
            );
            if (form.value.mother_occupation) {
                yPosition += addText(
                    `  Occupation: ${form.value.mother_occupation}`,
                    margin,
                    yPosition,
                );
            }
            if (form.value.mother_contact) {
                yPosition += addText(
                    `  Contact: ${form.value.mother_contact}`,
                    margin,
                    yPosition,
                );
            }
        }
        if (form.value.guardian_full_name) {
            yPosition += addText(
                `Guardian: ${form.value.guardian_full_name}`,
                margin,
                yPosition,
            );
            if (form.value.guardian_contact_no) {
                yPosition += addText(
                    `  Contact: ${form.value.guardian_contact_no}`,
                    margin,
                    yPosition,
                );
            }
            if (form.value.guardian_relationship) {
                yPosition += addText(
                    `  Relationship: ${form.value.guardian_relationship}`,
                    margin,
                    yPosition,
                );
            }
        }
        yPosition += 5;
    }

    // Check if new page is needed and add header
    const checkPageBreak = (spaceNeeded = 30) => {
        if (yPosition + spaceNeeded > 270) {
            doc.addPage();
            yPosition = 15;

            // Add simplified header for subsequent pages
            doc.setFontSize(12);
            doc.setFont(undefined, "bold");
            doc.setTextColor(0, 51, 102);
            doc.text("TAMBO NATIONAL HIGH SCHOOL", pageWidth / 2, yPosition, {
                align: "center",
            });
            yPosition += 6;

            doc.setFontSize(9);
            doc.setFont(undefined, "normal");
            doc.setTextColor(100, 100, 100);
            doc.text("Application Form (Continued)", pageWidth / 2, yPosition, {
                align: "center",
            });
            yPosition += 8;

            doc.setDrawColor(0, 51, 102);
            doc.setLineWidth(0.3);
            doc.line(margin, yPosition, pageWidth - margin, yPosition);
            yPosition += 10;

            doc.setTextColor(0, 0, 0);
        }
    };

    checkPageBreak();

    // Medical Information
    if (
        form.value.blood_type ||
        form.value.allergies ||
        form.value.medical_conditions
    ) {
        checkPageBreak(40);
        doc.setFontSize(14);
        doc.setFont(undefined, "bold");
        yPosition += addText("Medical Information", margin, yPosition);
        yPosition += 5;

        doc.setFontSize(10);
        doc.setFont(undefined, "normal");
        if (form.value.blood_type) {
            yPosition += addText(
                `Blood Type: ${form.value.blood_type}`,
                margin,
                yPosition,
            );
        }
        if (form.value.allergies) {
            yPosition += addText(
                `Allergies: ${form.value.allergies}`,
                margin,
                yPosition,
            );
        }
        if (form.value.medical_conditions) {
            yPosition += addText(
                `Medical Conditions: ${form.value.medical_conditions}`,
                margin,
                yPosition,
            );
        }
        if (form.value.emergency_contact_person) {
            yPosition += addText(
                `Emergency Contact: ${form.value.emergency_contact_person}`,
                margin,
                yPosition,
            );
            if (form.value.emergency_contact_number) {
                yPosition += addText(
                    `  Phone: ${form.value.emergency_contact_number}`,
                    margin,
                    yPosition,
                );
            }
        }
        yPosition += 5;
    }

    // Admission Information
    checkPageBreak(40);
    doc.setFontSize(14);
    doc.setFont(undefined, "bold");
    yPosition += addText("Admission Information", margin, yPosition);
    yPosition += 5;

    doc.setFontSize(10);
    doc.setFont(undefined, "normal");
    yPosition += addText(
        `Year Level: ${form.value.year_level_applying || "N/A"}`,
        margin,
        yPosition,
    );
    if (form.value.preferred_strand) {
        yPosition += addText(
            `Preferred Strand: ${form.value.preferred_strand}`,
            margin,
            yPosition,
        );
    }
    yPosition += addText(
        `School Year: ${form.value.school_year_applying || "N/A"}`,
        margin,
        yPosition,
    );
    if (form.value.previous_school) {
        yPosition += addText(
            `Previous School: ${form.value.previous_school}`,
            margin,
            yPosition,
        );
    }
    if (form.value.year_graduated) {
        yPosition += addText(
            `Year Graduated: ${form.value.year_graduated}`,
            margin,
            yPosition,
        );
    }
    if (form.value.previous_gwa) {
        yPosition += addText(
            `Previous GWA: ${form.value.previous_gwa}`,
            margin,
            yPosition,
        );
    }
    yPosition += 10;

    // Uploaded Documents
    checkPageBreak(40);
    const uploadedDocs = Object.entries(documents.value)
        .filter(([_, doc]) => doc.name)
        .map(([key, doc]) => {
            const labels = {
                form_137: "Form 137",
                birth_certificate: "Birth Certificate",
                good_moral_certificate: "Good Moral Certificate",
                accomplishment_credentials: "Accomplishment Credentials",
            };
            return { label: labels[key], name: doc.name };
        });

    if (uploadedDocs.length > 0) {
        doc.setFontSize(14);
        doc.setFont(undefined, "bold");
        yPosition += addText("Uploaded Documents", margin, yPosition);
        yPosition += 5;

        doc.setFontSize(10);
        doc.setFont(undefined, "normal");
        uploadedDocs.forEach((doc) => {
            yPosition += addText(
                `• ${doc.label}: ${doc.name}`,
                margin,
                yPosition,
            );
        });
    } else {
        doc.setFontSize(14);
        doc.setFont(undefined, "bold");
        yPosition += addText("Uploaded Documents", margin, yPosition);
        yPosition += 5;

        doc.setFontSize(10);
        doc.setFont(undefined, "normal");
        yPosition += addText("No documents uploaded yet.", margin, yPosition);
    }

    // Add footer to all pages with page numbers
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);

        // Bottom border line
        const footerY = doc.internal.pageSize.getHeight() - 25;
        doc.setDrawColor(0, 51, 102);
        doc.setLineWidth(0.3);
        doc.line(margin, footerY, pageWidth - margin, footerY);

        // Footer text
        doc.setFontSize(8);
        doc.setFont(undefined, "italic");
        doc.setTextColor(100, 100, 100);

        // Left side - Generated date
        doc.text(
            `Generated: ${new Date().toLocaleString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            })}`,
            margin,
            footerY + 5,
        );

        // Center - School name
        doc.text("Tambo National High School", pageWidth / 2, footerY + 5, {
            align: "center",
        });

        // Right side - Page number
        doc.text(`Page ${i} of ${pageCount}`, pageWidth - margin, footerY + 5, {
            align: "right",
        });
    }

    // Save PDF
    const filename = `TNHS_Application_${form.value.last_name}_${new Date().getTime()}.pdf`;
    doc.save(filename);
    toast.success("Application downloaded as PDF");
};

const submit = () => {
    if (!/^\d{12}$/.test(form.value.lrn)) {
        currentStep.value = 1;
        toast.error("LRN must be exactly 12 digits and unique.");
        return;
    }

    processing.value = true;

    const payload = {
        first_name: form.value.first_name,
        middle_name: form.value.middle_name,
        last_name: form.value.last_name,
        suffix: form.value.suffix,
        email: form.value.email,
        phone_no: form.value.phone_no,
        date_of_birth: form.value.date_of_birth,
        gender: form.value.gender,
        province: form.value.province,
        municipality: form.value.municipality,
        barangay: form.value.barangay,
        lrn: form.value.lrn,
        previous_gwa: form.value.previous_gwa,
        guardian_full_name: form.value.guardian_full_name,
        guardian_contact_no: form.value.guardian_contact_no,
        guardian_relationship: form.value.guardian_relationship,
        father_name: form.value.father_name,
        father_occupation: form.value.father_occupation,
        father_contact: form.value.father_contact,
        mother_name: form.value.mother_name,
        mother_occupation: form.value.mother_occupation,
        mother_contact: form.value.mother_contact,
        blood_type: form.value.blood_type,
        allergies: form.value.allergies,
        medical_conditions: form.value.medical_conditions,
        emergency_contact_person: form.value.emergency_contact_person,
        emergency_contact_number: form.value.emergency_contact_number,
        year_level_applying: form.value.year_level_applying,
        preferred_strand: form.value.preferred_strand,
        previous_school: form.value.previous_school,
        year_graduated: form.value.year_graduated,
        school_year_applying: form.value.school_year_applying,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
    };

    Object.keys(documents.value).forEach((key) => {
        if (
            !requiredAdmissionDocuments.value.some((doc) => doc.key === key)
        ) {
            return;
        }
        const file = documentFiles[key] || documents.value[key].file;
        if (file instanceof File) {
            payload[key] = file;
        }
    });

    router.post("/register", payload, {
        forceFormData: true,
        onSuccess: () => {
            toast.success(
                "Application submitted. Please wait for registrar approval before enrolling.",
            );
        },
        onFinish: () => {
            processing.value = false;
        },
        onError: (errors) => {
            processing.value = false;
            if (errors.lrn) {
                currentStep.value = 1;
            }
            const errorMessages = Object.values(errors).flat();
            if (errorMessages.length > 0) {
                toast.error(errorMessages[0]);
            } else {
                toast.error("Registration failed. Please check your inputs.");
            }
        },
    });
};
</script>

<style scoped>
.auth-container {
    position: relative;
    min-height: 100vh;
    background-image: url("/images/607055602_863940879900619_6210626938722324712_n.jpg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 1.5rem 1rem 2rem;
}

.overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 30, 70, 0.45);
}

.auth-content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 960px;
}

.auth-card {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.card-bar {
    background: #003366;
    color: #fff;
    padding: 0.55rem 1rem;
    font-size: 0.92rem;
    font-weight: 600;
    border-bottom: 3px solid #c9a227;
}

.card-body {
    padding: 1.25rem 1.5rem 1.35rem;
}

.auth-header {
    text-align: center;
    margin-bottom: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #d8d8d8;
}

.logo {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 2px solid #003366;
    margin: 0 auto 0.6rem auto;
    object-fit: cover;
    display: block;
}

.info-text {
    font-size: 0.95rem;
    font-weight: 700;
    color: #003366;
    margin: 0 0 0.15rem 0;
}

.info-text-small {
    font-size: 0.8rem;
    color: #555;
    margin: 0 0 0.35rem 0;
}

.subtitle {
    color: #444;
    font-size: 0.85rem;
    margin: 0;
}

.auth-form {
    margin-bottom: 0.75rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.85rem;
    margin-bottom: 0.85rem;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    color: #333;
    font-weight: 600;
    margin-bottom: 0.3rem;
    font-size: 0.8rem;
}

.form-input {
    width: 100%;
    padding: 0.45rem 0.55rem;
    border: 1px solid #bdbdbd;
    border-radius: 0;
    font-size: 0.9rem;
    box-sizing: border-box;
    background: #fff;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #003366;
}

.form-input.input-error {
    border-color: #9b1c1c;
}

.password-input-wrapper {
    position: relative;
}

.password-input {
    padding-right: 2.6rem;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 0.4rem;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.2rem;
    border: none;
    background: transparent;
    color: #555;
    cursor: pointer;
}

.password-toggle:hover {
    color: #003366;
}

.error-message {
    display: block;
    color: #9b1c1c;
    font-size: 0.75rem;
    margin-top: 0.2rem;
}

.field-hint {
    display: block;
    color: #555;
    font-size: 0.75rem;
    margin-top: 0.2rem;
}

.gender-group {
    display: flex;
    gap: 1.25rem;
    margin-top: 0.35rem;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    cursor: pointer;
    font-weight: 400;
    font-size: 0.85rem;
}

.radio-label input[type="radio"] {
    cursor: pointer;
    accent-color: #003366;
}

.section-divider {
    margin: 1.15rem 0 0.75rem 0;
    padding-top: 0.65rem;
    border-top: 1px solid #d8d8d8;
}

.section-title {
    color: #003366;
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 0.35rem 0;
    padding-bottom: 0.25rem;
    border-bottom: 2px solid #c9a227;
}

.section-description {
    color: #555;
    margin: 0 0 0.75rem 0;
    font-size: 0.85rem;
}

.submit-btn,
.btn-next {
    background: #003366;
    color: white;
    border: none;
    padding: 0.5rem 1.1rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.submit-btn:hover:not(:disabled),
.btn-next:hover:not(:disabled) {
    background: #00264d;
}

.submit-btn:disabled,
.btn-next:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.auth-footer {
    text-align: left;
    color: #555;
    margin-top: 0.85rem;
    padding-top: 0.85rem;
    border-top: 1px solid #d8d8d8;
    font-size: 0.85rem;
}

.auth-footer p {
    margin: 0 0 0.35rem 0;
}

.link {
    color: #003366;
    text-decoration: underline;
    font-weight: 600;
}

.link:hover {
    color: #00264d;
}

.step-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.45rem;
    padding: 0;
}

.step {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.step-number {
    width: 22px;
    height: 22px;
    border: 1px solid #bdbdbd;
    background: #fff;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.75rem;
}

.step.active .step-number {
    background: #003366;
    border-color: #003366;
    color: #fff;
}

.step.completed .step-number {
    background: #fff;
    border-color: #003366;
    color: #003366;
}

.step-label {
    font-size: 0.8rem;
    color: #555;
    font-weight: 600;
}

.step.active .step-label {
    color: #003366;
}

.step-line {
    flex: 1;
    height: 1px;
    background: #c5c5c5;
    margin: 0 0.65rem;
    max-width: 80px;
}

.step-line.active {
    background: #003366;
}

.step-status {
    text-align: center;
    font-size: 0.78rem;
    color: #555;
    margin: 0 0 1rem 0;
}

.step-navigation {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    margin-top: 1.15rem;
    padding-top: 0.85rem;
    border-top: 1px solid #d8d8d8;
}

.btn-secondary {
    background: #fff;
    color: #333;
    border: 1px solid #bdbdbd;
    padding: 0.5rem 1.1rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #f4f4f4;
}

.btn-download {
    background: #fff;
    color: #003366;
    border: 1px solid #003366;
    padding: 0.5rem 1.1rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-download:hover {
    background: #003366;
    color: #fff;
}

.documents-table-wrap {
    border: 1px solid #c5c5c5;
    overflow-x: auto;
}

.documents-table {
    width: 100%;
    border-collapse: collapse;
}

.documents-table th {
    background: #e8eef4;
    color: #003366;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 0.5rem 0.7rem;
    border-bottom: 1px solid #c5c5c5;
}

.documents-table td {
    padding: 0.55rem 0.7rem;
    border-bottom: 1px solid #ddd;
    font-size: 0.85rem;
    color: #222;
    vertical-align: middle;
}

.documents-table tbody tr:last-child td {
    border-bottom: none;
}

.file-input {
    display: none;
}

.file-label {
    display: inline-block;
    background: #fff;
    color: #003366;
    border: 1px solid #003366;
    padding: 0.25rem 0.55rem;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
}

.file-label:hover {
    background: #003366;
    color: #fff;
}

.file-name {
    display: inline-block;
    margin-left: 0.5rem;
    font-size: 0.78rem;
    color: #555;
}

.review-section {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.review-card {
    background: #fff;
    border: 1px solid #c5c5c5;
}

.review-card h4 {
    margin: 0;
    color: #fff;
    background: #003366;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.4rem 0.75rem;
    border-bottom: 3px solid #c9a227;
}

.review-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem 1rem;
    padding: 0.85rem 0.9rem;
}

.review-item {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.review-label {
    font-size: 0.75rem;
    color: #555;
    font-weight: 600;
}

.review-value {
    font-size: 0.9rem;
    color: #222;
}

.review-documents {
    display: flex;
    flex-direction: column;
    padding: 0.35rem 0;
}

.doc-review-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 0.9rem;
    border-top: 1px solid #eee;
    font-size: 0.85rem;
    color: #222;
}

.doc-review-item:first-child {
    border-top: none;
}

.doc-check {
    color: #003366;
    flex-shrink: 0;
}

@media (max-width: 900px) {
    .form-row,
    .review-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .form-row,
    .review-grid {
        grid-template-columns: 1fr;
    }

    .card-body {
        padding: 1rem;
    }

    .step {
        flex-direction: column;
        gap: 0.2rem;
    }

    .step-label {
        font-size: 0.7rem;
    }

    .step-line {
        max-width: 28px;
        margin: 0 0.35rem;
    }

    .step-navigation {
        flex-direction: column-reverse;
    }

    .btn-next,
    .btn-secondary,
    .btn-download,
    .submit-btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .auth-container {
        padding: 0.75rem 0.6rem 1.25rem;
    }

    .logo {
        width: 60px;
        height: 60px;
    }
}
</style>
