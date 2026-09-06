<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="confirm-overlay"
            @click.self="onCancel"
        >
            <div class="confirm-box">
                <div class="confirm-head" :class="{ danger }">
                    <h3>{{ title }}</h3>
                    <button
                        type="button"
                        class="confirm-close"
                        @click="onCancel"
                    >
                        ×
                    </button>
                </div>
                <div class="confirm-body">
                    <p>{{ message }}</p>
                </div>
                <div class="confirm-foot">
                    <button
                        type="button"
                        class="confirm-btn secondary"
                        :disabled="busy"
                        @click="onCancel"
                    >
                        {{ cancelLabel }}
                    </button>
                    <button
                        type="button"
                        class="confirm-btn"
                        :class="danger ? 'danger' : 'primary'"
                        :disabled="busy"
                        @click="$emit('confirm')"
                    >
                        {{ busy ? "Please wait..." : confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: "Confirm" },
    message: { type: String, default: "Are you sure?" },
    confirmLabel: { type: String, default: "Confirm" },
    cancelLabel: { type: String, default: "Cancel" },
    danger: { type: Boolean, default: true },
    busy: { type: Boolean, default: false },
});

const emit = defineEmits(["confirm", "cancel"]);

const onCancel = () => {
    emit("cancel");
};
</script>

<style scoped>
.confirm-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 1rem;
}

.confirm-box {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border: 1px solid #c5c5c5;
    border-radius: 0;
}

.confirm-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.55rem 0.85rem;
    background: #003366;
    color: #fff;
    border-bottom: 3px solid #c9a227;
    border-radius: 0;
}

.confirm-head.danger {
    background: #9b1c1c;
}

.confirm-head h3 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
}

.confirm-close {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.3rem;
    line-height: 1;
    cursor: pointer;
    border-radius: 0;
}

.confirm-close:hover {
    color: #ddd;
}

.confirm-body {
    padding: 1rem 1.1rem;
}

.confirm-body p {
    margin: 0;
    color: #333;
    font-size: 0.9rem;
    line-height: 1.45;
}

.confirm-foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 0.7rem 1.1rem;
    border-top: 1px solid #e0e0e0;
    background: #fff;
}

.confirm-btn {
    padding: 0.5rem 0.9rem;
    font-weight: 600;
    cursor: pointer;
    border-radius: 0;
}

.confirm-btn.secondary {
    background: #fff;
    color: #333;
    border: 1px solid #bdbdbd;
}

.confirm-btn.secondary:hover:not(:disabled) {
    background: #f4f4f4;
}

.confirm-btn.primary {
    background: #003366;
    color: #fff;
    border: none;
}

.confirm-btn.primary:hover:not(:disabled) {
    background: #00264d;
}

.confirm-btn.danger {
    background: #9b1c1c;
    color: #fff;
    border: none;
}

.confirm-btn.danger:hover:not(:disabled) {
    background: #7f1d1d;
}

.confirm-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>
