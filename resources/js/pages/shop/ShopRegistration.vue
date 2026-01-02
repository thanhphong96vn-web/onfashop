<template>
    <div>
        <v-container>
            <v-row>
                <v-col xl="10" class="mx-auto">
                    <div
                        class="my-5 my-lg-16 rounded-lg pa-5 border overflow-hidden shadow-light"
                    >
                        <div
                            class="rounded border border-primary bg-soft-primary py-4 px-4 px-md-16 d-md-flex justify-space-between align-center mb-5"
                        >
                            <h3 class="text-h5 fw-700 mb-5 mb-md-0">
                                {{ $t("already_a_seller") }}
                            </h3>
                            <v-btn
                                :href="$store.getters['app/appUrl'] + '/login'"
                                color="primary"
                                class="px-12 py-3 white--text fs-16"
                                style="padding-bottom: 30px !important;"
                                target="_blank"
                                elevation="0"
                                >{{ $t("login") }}</v-btn
                            >
                        </div>
                        <v-row no-gutters align="start">
                            <!-- Banner temporarily hidden -->
                            <v-col
                                cols="12"
                                lg="6"
                                order="2"
                                order-lg="1"
                                class="lh-0 d-none"
                            >
                                <banner
                                    :loading="false"
                                    :banner="$store.getters['app/banners'].shop_registration_page"
                                    class="mt-5 mt-lg-0"
                                />
                            </v-col>
                            <v-col cols="12" order="1" order-lg="2" lg="12">
                                <div class="px-lg-7">
                                    <h1 class="text-h3 fw-700 mb-8">
                                        {{ $t("register_your_shop") }}
                                    </h1>
                                    <v-form lazy-validation v-on:submit.prevent="register()">
                                        <v-card class="border" elevation="0">
                                            <v-card-title>
                                                <span class="text-h6 fw-600 opacity-80">{{
                                                    $t("personal_information")
                                                }}</span>
                                            </v-card-title>
                                            <v-card-text>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("full_name") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        class="text-field"
                                                        :placeholder="$t('full_name')"
                                                        type="text"
                                                        v-model="form.name"
                                                        :error-messages="nameErrors"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("phone_number") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        class="text-field"
                                                        :placeholder="$t('phone_number')"
                                                        type="tel"
                                                        v-model="form.phone"
                                                        @input="form.phone = form.phone.replace(/\D/g, '')"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                        maxlength="10"
                                                    ></v-text-field>
                                                    <p
                                                        v-for="error of v$.form.phone.$errors"
                                                        :key="error.$uid"
                                                        class="text-red"
                                                    >
                                                        {{ error.$message }}
                                                    </p>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="fs-13 fw-500">
                                                        {{ $t("email_address") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        class="text-field"
                                                        :placeholder="$t('email_address')"
                                                        type="email"
                                                        v-model="form.email"
                                                        :error-messages="emailErrors"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("password") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        placeholder="* * * * * * * *"
                                                        v-model="form.password"
                                                        :error-messages="passwordErrors"
                                                        type="password"
                                                        class="input-group--focused text-field"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("confirm_password") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        placeholder="* * * * * * * *"
                                                        v-model="form.confirmPassword"
                                                        :error-messages="confirmPasswordErrors"
                                                        type="password"
                                                        class="input-group--focused text-field"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                            </v-card-text>

                                            <v-divider />

                                            <v-card-title>
                                                <span class="text-h6 fw-600 opacity-80">{{
                                                    $t("shop_information")
                                                }}</span>
                                            </v-card-title>
                                            <v-card-text>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("shop_name") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        class="text-field"
                                                        :placeholder="$t('shop_name')"
                                                        type="text"
                                                        v-model="form.shopName"
                                                        :error-messages="shopNameErrors"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("shop_phone") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        class="text-field"
                                                        :placeholder="$t('shop_phone')"
                                                        type="number"
                                                        v-model="form.shopPhone"
                                                        :error-messages="shopPhoneErrors"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                                <div class="mb-4">
                                                    <div class="mb-1 fs-13 fw-500">
                                                        {{ $t("shop_address") }}
                                                    </div>
                                                    <v-text-field
                                                        variant="plain"
                                                        class="text-field"
                                                        :placeholder="$t('shop_address')"
                                                        type="text"
                                                        v-model="form.shopAddress"
                                                        :error-messages="shopAddressErrors"
                                                        hide-details="auto"
                                                        required
                                                        outlined
                                                    ></v-text-field>
                                                </div>
                                                <div class="mb-4">
                                                    {{ $t("by_signing_up_you_agree_to_our") }}
                                                    <router-link
                                                        :to="{
                                                            name: 'CustomPage',
                                                            params: {
                                                                pageSlug: 'terms-and-conditions',
                                                            },
                                                        }"
                                                        class="primary--text text-decoration-underline"
                                                        >{{
                                                            $t("terms_and_conditions")
                                                        }}</router-link
                                                    >
                                                </div>
                                            </v-card-text>
                                        </v-card>
                                        <v-btn
                                            x-large
                                            class="px-12 mb-4 w-100 mt-5"
                                            elevation="0"
                                            type="submit"
                                            color="primary"
                                            @click="register"
                                            :loading="loading"
                                            :disabled="loading"
                                            >{{ $t("register_shop") }}</v-btn
                                        >
                                    </v-form>
                                </div>
                            </v-col>
                        </v-row>
                    </div>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>

<script>
import { required, email, minLength } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { mapActions, mapGetters, mapMutations } from "vuex";

import { loadRecaptcha } from "@/utils/loadRecaptcha";
import { useRecaptcha } from "@/utils/useRecaptcha";
import { removeRecaptcha } from "@/utils/removeRecaptcha";

const { verifyRecaptcha } = useRecaptcha();

export default {
    data: () => ({
        loading: false,
        v$: useVuelidate(),
        form: {
            name: "",
            phone: "",
            email: "",
            password: "",
            confirmPassword: "",
            shopName: "",
            shopPhone: "",
            shopAddress: "",
        },
    }),
    components: {
    },
    validations: {
        form: {
            name: { required },
            email: { required, email },
            phone: {
                required,
                minLength: minLength(10),
                phoneFormat: (value) => {
                    if (!value) return true;
                    // Chỉ chấp nhận số và đúng 10 số
                    return /^\d{10}$/.test(value);
                },
            },
            password: { required, minLength: minLength(6) },
            confirmPassword: {
                required,
            },
            shopName: { required },
            shopPhone: { required },
            shopAddress: { required },
        },
    },
    computed: {
        ...mapGetters("app", ["generalSettings"]),
        nameErrors() {
            const errors = [];
            if (!this.v$.form.name.$dirty) return errors;
            !this.v$.form.name.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
            return errors;
        },
        emailErrors() {
            const errors = [];
            if (!this.v$.form.email.$dirty) return errors;
            !this.v$.form.email.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
            !this.v$.form.email.email &&
                errors.push(
                    this.$i18n.t("this_field_is_required_a_valid_email")
                );
            return errors;
        },
        passwordErrors() {
            const errors = [];
            if (!this.v$.form.password.$dirty) return errors;
            !this.v$.form.password.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
            !this.v$.form.password.minLength &&
                errors.push(
                    this.$i18n.t("password_must_be_minimum_6_characters")
                );
            return errors;
        },
        confirmPasswordErrors() {
            const errors = [];
            if (!this.v$.form.confirmPassword.$dirty) return errors;
            !this.v$.form.confirmPassword.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
                !this.v$.form.password.minLength &&
                errors.push(
                    this.$i18n.t("password_and_confirm_password_should_match")
                );
            return errors;
        },
        shopNameErrors() {
            const errors = [];
            if (!this.v$.form.shopName.$dirty) return errors;
            !this.v$.form.shopName.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
            return errors;
        },
        shopPhoneErrors() {
            const errors = [];
            if (!this.v$.form.shopPhone.$dirty) return errors;
            !this.v$.form.shopPhone.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
            return errors;
        },
        shopAddressErrors() {
            const errors = [];
            if (!this.v$.form.shopAddress.$dirty) return errors;
            !this.v$.form.shopAddress.required &&
                errors.push(this.$i18n.t("this_field_is_required"));
            return errors;
        },
    },
    async mounted() {
        if(this.generalSettings.google_recaptcha && this.generalSettings.recaptcha_shop_register){
            try {
                await loadRecaptcha(import.meta.env.VITE_RECAPTCHA_KEY);
                this.recaptchaReady = true;
                console.log("reCAPTCHA ready!");
            } catch (err) {
                console.error(err);
            }
        }
    },
     beforeUnmount() {
        removeRecaptcha();
    },
    methods: {
        ...mapActions("auth", ["login"]),
        ...mapMutations("cart", ["removeTempUserId"]),
        ...mapMutations("auth", ["updateChatWindow", "showLoginDialog"]),
        async register() {

            if(this.generalSettings.google_recaptcha && this.generalSettings.recaptcha_shop_register){
                const response_recapcha = await verifyRecaptcha("register-shop");
                if(!response_recapcha.success){
                    this.snack({
                        message: response_recapcha.message,
                        color: "red",
                    });
                    return;
                }
            }

            // Validate tất cả fields khi submit
            this.v$.$touch();
            const isFormCorrect = await this.v$.$validate();
            if (!isFormCorrect) return;

            // Chỉ lấy số, loại bỏ ký tự đặc biệt
            this.form.phone = this.form.phone.replace(/\D/g, "");

            this.loading = true;
            const res = await this.call_api("post", "shop/register", this.form);
            if (res.data.success) {
                this.snack({
                    color: "green",
                    message: res.data.message,
                });
                this.$router.push({ name: "ShopConfirmation" });
                // this.resetForm()
            } else {
                this.snack({
                    message: res.data.message,
                    color: "red",
                });
            }
            this.loading = false;
        },
        resetForm() {
            this.form.name = "";
            this.form.phone = "";
            this.form.email = "";
            this.form.password = "";
            this.form.confirmPassword = "";
            this.form.shopName = "";
            this.form.shopPhone = "";
            this.form.shopAddress = "";
        },
    },
};
</script>
