<script setup>
import { ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = page.props.auth.user;

// Profile info form (without photo)
const form = useForm({
    username: user.username,
    first_name: user.first_name || '',
    last_name: user.last_name || '',
    email: user.email,
    phone: user.phone || '',
    birth_date: user.birth_date || '',
    country: user.country || 'Latvia',
    city: user.city || '',
    address: user.address || '',
    postal_code: user.postal_code || '',
});

// Separate photo form
const photoForm = useForm({
    photo: null,
});

const photoInput = ref(null);
const photoPreview = ref(null);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);

    // Upload photo immediately
    uploadPhoto(photo);
};

const uploadPhoto = (photo) => {
    photoForm.photo = photo;

    photoForm.post(route('profile.avatar.update'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
        onError: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const deletePhoto = () => {
    if (!confirm(t('profile.confirm_delete_photo'))) {
        return;
    }

    router.delete(route('profile.avatar.delete'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};

const submit = () => {
    form.patch(route('profile.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="profile-section">
        <header class="section-header">
            <div class="header-icon">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h2 class="section-title">{{ t('profile.profile_information') }}</h2>
                <p class="section-description">
                    {{ t('profile.update_profile_description') }}
                </p>
            </div>
        </header>

        <form @submit.prevent="submit" class="profile-form">
            <!-- Profile Picture -->
            <div class="form-group photo-section">
                <label class="form-label">
                    <i class="fas fa-camera"></i>
                    {{ t('profile.profile_picture') }}
                </label>

                <div class="photo-container">
                    <!-- Current Photo -->
                    <div v-if="!photoPreview" class="current-photo">
                        <img
                            v-if="user.profile_picture"
                            :src="`/storage/${user.profile_picture}`"
                            :alt="user.username"
                            class="photo-img"
                        />
                        <div v-else class="photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>

                    <!-- New Photo Preview -->
                    <div v-else class="photo-preview">
                        <span v-if="photoForm.processing" class="photo-uploading">
                            <i class="fas fa-spinner fa-spin"></i>
                            {{ t('profile.uploading') }}
                        </span>
                        <img v-else :src="photoPreview" alt="Preview" class="photo-img" />
                    </div>

                    <!-- Photo Actions -->
                    <div class="photo-actions">
                        <input
                            ref="photoInput"
                            type="file"
                            class="hidden"
                            accept="image/*"
                            @change="updatePhotoPreview"
                        />

                        <button
                            type="button"
                            class="btn-secondary btn-sm"
                            @click="selectNewPhoto"
                            :disabled="photoForm.processing"
                        >
                            <i class="fas fa-upload"></i>
                            {{ photoForm.processing ? t('profile.uploading') : t('profile.select_photo') }}
                        </button>

                        <button
                            v-if="user.profile_picture"
                            type="button"
                            class="btn-danger btn-sm"
                            @click="deletePhoto"
                            :disabled="photoForm.processing"
                        >
                            <i class="fas fa-trash"></i>
                            {{ t('profile.delete_photo') }}
                        </button>
                    </div>
                </div>

                <p class="form-hint">{{ t('profile.photo_hint') }}</p>

                <p v-if="photoForm.errors.photo" class="form-error">
                    {{ photoForm.errors.photo }}
                </p>
            </div>

            <!-- Form Fields Grid -->
            <div class="form-grid">
                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i>
                        {{ t('profile.username') }}
                    </label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        class="form-input"
                        required
                        autocomplete="username"
                    />
                    <p v-if="form.errors.username" class="form-error">
                        {{ form.errors.username }}
                    </p>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        {{ t('profile.email') }}
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-input"
                        required
                        autocomplete="email"
                    />
                    <p v-if="form.errors.email" class="form-error">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- First Name -->
                <div class="form-group">
                    <label for="first_name" class="form-label">
                        <i class="fas fa-user"></i>
                        {{ t('profile.first_name') }}
                    </label>
                    <input
                        id="first_name"
                        v-model="form.first_name"
                        type="text"
                        class="form-input"
                        autocomplete="given-name"
                    />
                    <p v-if="form.errors.first_name" class="form-error">
                        {{ form.errors.first_name }}
                    </p>
                </div>

                <!-- Last Name -->
                <div class="form-group">
                    <label for="last_name" class="form-label">
                        <i class="fas fa-user"></i>
                        {{ t('profile.last_name') }}
                    </label>
                    <input
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        class="form-input"
                        autocomplete="family-name"
                    />
                    <p v-if="form.errors.last_name" class="form-error">
                        {{ form.errors.last_name }}
                    </p>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i>
                        {{ t('profile.phone') }}
                    </label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="form-input"
                        placeholder="+371 29123456"
                        autocomplete="tel"
                    />
                    <p v-if="form.errors.phone" class="form-error">
                        {{ form.errors.phone }}
                    </p>
                </div>

                <!-- Birth Date -->
                <div class="form-group">
                    <label for="birth_date" class="form-label">
                        <i class="fas fa-calendar"></i>
                        {{ t('profile.birth_date') }}
                    </label>
                    <input
                        id="birth_date"
                        v-model="form.birth_date"
                        type="date"
                        class="form-input"
                        autocomplete="bday"
                    />
                    <p v-if="form.errors.birth_date" class="form-error">
                        {{ form.errors.birth_date }}
                    </p>
                </div>

                <!-- Country -->
                <div class="form-group">
                    <label for="country" class="form-label">
                        <i class="fas fa-flag"></i>
                        {{ t('profile.country') }}
                    </label>
                    <select
                        id="country"
                        v-model="form.country"
                        class="form-input"
                        autocomplete="country"
                    >
                        <option value="Afghanistan">Afghanistan</option>
                        <option value="Albania">Albania</option>
                        <option value="Algeria">Algeria</option>
                        <option value="Andorra">Andorra</option>
                        <option value="Angola">Angola</option>
                        <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                        <option value="Argentina">Argentina</option>
                        <option value="Armenia">Armenia</option>
                        <option value="Australia">Australia</option>
                        <option value="Austria">Austria</option>
                        <option value="Azerbaijan">Azerbaijan</option>
                        <option value="Bahamas">Bahamas</option>
                        <option value="Bahrain">Bahrain</option>
                        <option value="Bangladesh">Bangladesh</option>
                        <option value="Barbados">Barbados</option>
                        <option value="Belarus">Belarus</option>
                        <option value="Belgium">Belgium</option>
                        <option value="Belize">Belize</option>
                        <option value="Benin">Benin</option>
                        <option value="Bhutan">Bhutan</option>
                        <option value="Bolivia">Bolivia</option>
                        <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                        <option value="Botswana">Botswana</option>
                        <option value="Brazil">Brazil</option>
                        <option value="Brunei">Brunei</option>
                        <option value="Bulgaria">Bulgaria</option>
                        <option value="Burkina Faso">Burkina Faso</option>
                        <option value="Burundi">Burundi</option>
                        <option value="Cabo Verde">Cabo Verde</option>
                        <option value="Cambodia">Cambodia</option>
                        <option value="Cameroon">Cameroon</option>
                        <option value="Canada">Canada</option>
                        <option value="Central African Republic">Central African Republic</option>
                        <option value="Chad">Chad</option>
                        <option value="Chile">Chile</option>
                        <option value="China">China</option>
                        <option value="Colombia">Colombia</option>
                        <option value="Comoros">Comoros</option>
                        <option value="Congo (Congo-Brazzaville)">Congo (Congo-Brazzaville)</option>
                        <option value="Costa Rica">Costa Rica</option>
                        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
                        <option value="Croatia">Croatia</option>
                        <option value="Cuba">Cuba</option>
                        <option value="Cyprus">Cyprus</option>
                        <option value="Czechia (Czech Republic)">Czechia (Czech Republic)</option>
                        <option value="Democratic Republic of the Congo">Democratic Republic of the Congo</option>
                        <option value="Denmark">Denmark</option>
                        <option value="Djibouti">Djibouti</option>
                        <option value="Dominica">Dominica</option>
                        <option value="Dominican Republic">Dominican Republic</option>
                        <option value="Ecuador">Ecuador</option>
                        <option value="Egypt">Egypt</option>
                        <option value="El Salvador">El Salvador</option>
                        <option value="Equatorial Guinea">Equatorial Guinea</option>
                        <option value="Eritrea">Eritrea</option>
                        <option value="Estonia">Estonia</option>
                        <option value='Eswatini (fmr. "Swaziland")'>Eswatini (fmr. "Swaziland")</option>
                        <option value="Ethiopia">Ethiopia</option>
                        <option value="Fiji">Fiji</option>
                        <option value="Finland">Finland</option>
                        <option value="France">France</option>
                        <option value="Gabon">Gabon</option>
                        <option value="Gambia">Gambia</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Germany">Germany</option>
                        <option value="Ghana">Ghana</option>
                        <option value="Greece">Greece</option>
                        <option value="Grenada">Grenada</option>
                        <option value="Guatemala">Guatemala</option>
                        <option value="Guinea">Guinea</option>
                        <option value="Guinea-Bissau">Guinea-Bissau</option>
                        <option value="Guyana">Guyana</option>
                        <option value="Haiti">Haiti</option>
                        <option value="Holy See">Holy See</option>
                        <option value="Honduras">Honduras</option>
                        <option value="Hungary">Hungary</option>
                        <option value="Iceland">Iceland</option>
                        <option value="India">India</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Iran">Iran</option>
                        <option value="Iraq">Iraq</option>
                        <option value="Ireland">Ireland</option>
                        <option value="Israel">Israel</option>
                        <option value="Italy">Italy</option>
                        <option value="Jamaica">Jamaica</option>
                        <option value="Japan">Japan</option>
                        <option value="Jordan">Jordan</option>
                        <option value="Kazakhstan">Kazakhstan</option>
                        <option value="Kenya">Kenya</option>
                        <option value="Kiribati">Kiribati</option>
                        <option value="Kuwait">Kuwait</option>
                        <option value="Kyrgyzstan">Kyrgyzstan</option>
                        <option value="Laos">Laos</option>
                        <option value="Latvia">Latvia</option>
                        <option value="Lebanon">Lebanon</option>
                        <option value="Lesotho">Lesotho</option>
                        <option value="Liberia">Liberia</option>
                        <option value="Libya">Libya</option>
                        <option value="Liechtenstein">Liechtenstein</option>
                        <option value="Lithuania">Lithuania</option>
                        <option value="Luxembourg">Luxembourg</option>
                        <option value="Madagascar">Madagascar</option>
                        <option value="Malawi">Malawi</option>
                        <option value="Malaysia">Malaysia</option>
                        <option value="Maldives">Maldives</option>
                        <option value="Mali">Mali</option>
                        <option value="Malta">Malta</option>
                        <option value="Marshall Islands">Marshall Islands</option>
                        <option value="Mauritania">Mauritania</option>
                        <option value="Mauritius">Mauritius</option>
                        <option value="Mexico">Mexico</option>
                        <option value="Micronesia">Micronesia</option>
                        <option value="Moldova">Moldova</option>
                        <option value="Monaco">Monaco</option>
                        <option value="Mongolia">Mongolia</option>
                        <option value="Montenegro">Montenegro</option>
                        <option value="Morocco">Morocco</option>
                        <option value="Mozambique">Mozambique</option>
                        <option value="Myanmar (formerly Burma)">Myanmar (formerly Burma)</option>
                        <option value="Namibia">Namibia</option>
                        <option value="Nauru">Nauru</option>
                        <option value="Nepal">Nepal</option>
                        <option value="Netherlands">Netherlands</option>
                        <option value="New Zealand">New Zealand</option>
                        <option value="Nicaragua">Nicaragua</option>
                        <option value="Niger">Niger</option>
                        <option value="Nigeria">Nigeria</option>
                        <option value="North Korea">North Korea</option>
                        <option value="North Macedonia">North Macedonia</option>
                        <option value="Norway">Norway</option>
                        <option value="Oman">Oman</option>
                        <option value="Pakistan">Pakistan</option>
                        <option value="Palau">Palau</option>
                        <option value="Palestine State">Palestine State</option>
                        <option value="Panama">Panama</option>
                        <option value="Papua New Guinea">Papua New Guinea</option>
                        <option value="Paraguay">Paraguay</option>
                        <option value="Peru">Peru</option>
                        <option value="Philippines">Philippines</option>
                        <option value="Poland">Poland</option>
                        <option value="Portugal">Portugal</option>
                        <option value="Qatar">Qatar</option>
                        <option value="Romania">Romania</option>
                        <option value="Russia">Russia</option>
                        <option value="Rwanda">Rwanda</option>
                        <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                        <option value="Saint Lucia">Saint Lucia</option>
                        <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                        <option value="Samoa">Samoa</option>
                        <option value="San Marino">San Marino</option>
                        <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                        <option value="Saudi Arabia">Saudi Arabia</option>
                        <option value="Senegal">Senegal</option>
                        <option value="Serbia">Serbia</option>
                        <option value="Seychelles">Seychelles</option>
                        <option value="Sierra Leone">Sierra Leone</option>
                        <option value="Singapore">Singapore</option>
                        <option value="Slovakia">Slovakia</option>
                        <option value="Slovenia">Slovenia</option>
                        <option value="Solomon Islands">Solomon Islands</option>
                        <option value="Somalia">Somalia</option>
                        <option value="South Africa">South Africa</option>
                        <option value="South Korea">South Korea</option>
                        <option value="South Sudan">South Sudan</option>
                        <option value="Spain">Spain</option>
                        <option value="Sri Lanka">Sri Lanka</option>
                        <option value="Sudan">Sudan</option>
                        <option value="Suriname">Suriname</option>
                        <option value="Sweden">Sweden</option>
                        <option value="Switzerland">Switzerland</option>
                        <option value="Syria">Syria</option>
                        <option value="Tajikistan">Tajikistan</option>
                        <option value="Tanzania">Tanzania</option>
                        <option value="Thailand">Thailand</option>
                        <option value="Timor-Leste">Timor-Leste</option>
                        <option value="Togo">Togo</option>
                        <option value="Tonga">Tonga</option>
                        <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                        <option value="Tunisia">Tunisia</option>
                        <option value="Turkey">Turkey</option>
                        <option value="Turkmenistan">Turkmenistan</option>
                        <option value="Tuvalu">Tuvalu</option>
                        <option value="Uganda">Uganda</option>
                        <option value="Ukraine">Ukraine</option>
                        <option value="United Arab Emirates">United Arab Emirates</option>
                        <option value="United Kingdom">United Kingdom</option>
                        <option value="United States of America">United States of America</option>
                        <option value="Uruguay">Uruguay</option>
                        <option value="Uzbekistan">Uzbekistan</option>
                        <option value="Vanuatu">Vanuatu</option>
                        <option value="Venezuela">Venezuela</option>
                        <option value="Vietnam">Vietnam</option>
                        <option value="Yemen">Yemen</option>
                        <option value="Zambia">Zambia</option>
                        <option value="Zimbabwe">Zimbabwe</option>
                        <option value="Other">{{ t('profile.other') }}</option>
                    </select>
                    <p v-if="form.errors.country" class="form-error">
                        {{ form.errors.country }}
                    </p>
                </div>

                <!-- City -->
                <div class="form-group">
                    <label for="city" class="form-label">
                        <i class="fas fa-city"></i>
                        {{ t('profile.city') }}
                    </label>
                    <input
                        id="city"
                        v-model="form.city"
                        type="text"
                        class="form-input"
                        autocomplete="address-level2"
                    />
                    <p v-if="form.errors.city" class="form-error">
                        {{ form.errors.city }}
                    </p>
                </div>

                <!-- Address (Full Width) -->
                <div class="form-group form-group-full">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ t('profile.address') }}
                    </label>
                    <input
                        id="address"
                        v-model="form.address"
                        type="text"
                        class="form-input"
                        autocomplete="street-address"
                    />
                    <p v-if="form.errors.address" class="form-error">
                        {{ form.errors.address }}
                    </p>
                </div>

                <!-- Postal Code -->
                <div class="form-group">
                    <label for="postal_code" class="form-label">
                        <i class="fas fa-mail-bulk"></i>
                        {{ t('profile.postal_code') }}
                    </label>
                    <input
                        id="postal_code"
                        v-model="form.postal_code"
                        type="text"
                        class="form-input"
                        placeholder="LV-1050"
                        autocomplete="postal-code"
                    />
                    <p v-if="form.errors.postal_code" class="form-error">
                        {{ form.errors.postal_code }}
                    </p>
                </div>
            </div>

            <!-- Email Verification Warning -->
            <div
                v-if="mustVerifyEmail && user.email_verified_at === null"
                class="alert alert-warning"
            >
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <p>{{ t('profile.email_unverified') }}</p>
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="alert-link"
                    >
                        {{ t('profile.resend_verification') }}
                    </Link>
                </div>
            </div>

            <!-- Status Messages -->
            <div v-if="status === 'profile-updated'" class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ t('profile.saved') }}
            </div>

            <Transition name="fade">
                <div v-if="form.recentlySuccessful" class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ t('profile.saved') }}
                </div>
            </Transition>

            <!-- Submit Button -->
            <div class="form-actions">
                <button
                    type="submit"
                    class="btn-primary"
                    :disabled="form.processing"
                >
                    <i v-if="!form.processing" class="fas fa-save"></i>
                    <i v-else class="fas fa-spinner fa-spin"></i>
                    {{ form.processing ? t('common.saving') : t('common.save') }}
                </button>
            </div>
        </form>
    </section>
</template>

<style scoped>
/* ... (same CSS as before) ... */
.profile-section {
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #dc2626;
}

.section-header {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f3f4f6;
}

.header-icon {
    width: 3.5rem;
    height: 3.5rem;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.5rem 0;
}

.section-description {
    color: #6b7280;
    margin: 0;
    font-size: 0.95rem;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.photo-section {
    padding: 1.5rem;
    background: #f9fafb;
    border-radius: 0.75rem;
    border: 2px dashed #e5e7eb;
}

.photo-container {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-top: 1rem;
}

.current-photo,
.photo-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    border: 4px solid #dc2626;
    flex-shrink: 0;
    position: relative;
}

.photo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.photo-uploading {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    gap: 0.5rem;
}

.photo-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.hidden {
    display: none;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.95rem;
}

.form-label i {
    color: #dc2626;
    width: 1.25rem;
    text-align: center;
}

.form-input {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: all 0.2s;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-input:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
}

.form-error {
    color: #dc2626;
    font-size: 0.875rem;
    margin: 0;
}

.form-hint {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.5rem 0 0 0;
}

.alert {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.95rem;
}

.alert i {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-link {
    color: #dc2626;
    text-decoration: underline;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    padding: 0;
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 2px solid #f3f4f6;
}

.btn-primary,
.btn-secondary,
.btn-danger {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

.btn-secondary:hover:not(:disabled) {
    background: #e5e7eb;
}

.btn-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-danger {
    background: #fee2e2;
    color: #dc2626;
}

.btn-danger:hover:not(:disabled) {
    background: #fecaca;
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    .photo-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .section-header {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>
