#!/usr/bin/env bash

set -e

ERR_MSG=$(tail -n 20 job_output.log 2>/dev/null | sed 's/"/\\"/g')

MSG="
-=============================================-
*执行.gitlab-ci.yml完成*
仓库 *${CI_PROJECT_NAME}*
分支: *${CI_COMMIT_REF_NAME}*
提交id: [${CI_COMMIT_SHORT_SHA}](${CI_PROJECT_URL}/-/commit/${CI_COMMIT_SHA})
作者: ${CI_COMMIT_AUTHOR}

[执行日志详情](${CI_PIPELINE_URL})"

curl -s -X POST "https://api.telegram.org/bot${TG_BOT_TOKEN}/sendMessage" \
  -d chat_id="${TG_CHAT_ID}" \
  -d parse_mode="Markdown" \
  -d text="$MSG"

curl -s -X POST "https://api.telegram.org/bot${TG_BOT_TOKEN}/sendDocument" \
  -F chat_id="${TG_CHAT_ID}" \
  -F document="@job_output.log" \
  -F caption="执行日志文件"