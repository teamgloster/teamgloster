<template>
    <Toaster
        position="top-right"
        close-button-position="top-right"
        :duration="2000"
        :close-button="true"
        :rich-colors="false"
        :expand="true"
        theme="light"
    />
</template>

<script setup>
import { Toaster } from "vue-sonner";
import "vue-sonner/style.css";
</script>

<style>
[data-sonner-toast][data-styled="true"] {
    background: #fff !important;
    border: 1px solid #c5c5c5 !important;
    color: #222 !important;
    padding-right: 2.4rem !important;
}

[data-sonner-toast][data-type="success"],
[data-sonner-toast][data-type="success"] [data-title],
[data-sonner-toast][data-type="success"] [data-icon] {
    color: #1f6b3a !important;
}

[data-sonner-toast][data-type="error"],
[data-sonner-toast][data-type="error"] [data-title],
[data-sonner-toast][data-type="error"] [data-icon] {
    color: #9b1c1c !important;
}

[data-sonner-toast][data-type="warning"],
[data-sonner-toast][data-type="warning"] [data-title],
[data-sonner-toast][data-type="warning"] [data-icon] {
    color: #9a6700 !important;
}

[data-sonner-toast][data-type="info"],
[data-sonner-toast][data-type="info"] [data-title],
[data-sonner-toast][data-type="info"] [data-icon] {
    color: #003366 !important;
}

[data-sonner-toast][data-styled="true"] [data-close-button] {
    left: auto !important;
    right: 10px !important;
    top: 50% !important;
    bottom: auto !important;
    transform: translateY(-50%) !important;
    background: #fff !important;
    border: 1px solid #c5c5c5 !important;
    color: #555 !important;
}

[data-sonner-toast][data-styled="true"] [data-close-button]:hover {
    background: #f4f4f4 !important;
    border-color: #b0b0b0 !important;
    color: #111 !important;
}
</style>
