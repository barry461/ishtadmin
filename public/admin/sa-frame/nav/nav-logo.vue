<!-- 左上：logo部分 -->
<template>
	<div class="com-logo-box" :title="$root.title" @click="$root.showHome()">
		<img :src="$root.logo" class="admin-logo" v-if="$root.logo">
		<span class="admin-title">{{ $root.title }}</span>
	</div>
</template>

<script>
module.exports = {
	data() {
		return {
		}
	},
	methods: {
	},
	created() {
	}
}
</script>

<style scoped>
.com-logo-box {
	cursor: pointer;
	display: flex;
	align-items: center;
	height: 100%;

	@media (max-width:768px) {
		display: none;
	}
}

.admin-logo {
	width: 36px;
	height: 36px;
	border-radius: 50%;
	vertical-align: middle;
}

.admin-title {
	padding: 0 0.8em;
	margin-left: 0.5em;
	font-size: 1.1em;
	font-weight: 600;
	white-space: nowrap;
}
</style>
