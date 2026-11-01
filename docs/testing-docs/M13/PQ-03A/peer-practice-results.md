# EUR-68 (PQ-03A) — Peer Practice & Hands-on Verification

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-68 (PQ-03A)](https://linear.app/eurusdevsec/issue/EUR-68/pq-03a-complete-the-technical-demo-learning-catalog-with-reproducible)  
**Parent Acceptance Issue**: [EUR-65](https://linear.app/eurusdevsec/issue/EUR-65/m13-acceptance-complete-existing-journeys-curated-learning-content-and)  
**Evaluated Candidate**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)  
**Verification Method**: 4 Representative hands-on exercises completed by an independent contributor without author assistance (AC-03).

---

## 1. Practice Exercise 1: `networking-learning`
* **Lesson**: *IP Addressing, Subnetting, and CIDR Math*
* **Learner Objective**: Given CIDR `192.168.1.0/24`, segment the block into 4 equal subnets and identify network, broadcast, and usable host IP ranges.
* **Contributor Action**:
  - Derived subnet mask `/26` (`255.255.255.192`) yielding 64 IPs per block (62 usable hosts).
  - Computed Subnets:
    1. Subnet A: `192.168.1.0/26` (Hosts `192.168.1.1` - `192.168.1.62`, Broadcast `.63`).
    2. Subnet B: `192.168.1.64/26` (Hosts `192.168.1.65` - `192.168.1.126`, Broadcast `.127`).
    3. Subnet C: `192.168.1.128/26` (Hosts `192.168.1.129` - `192.168.1.190`, Broadcast `.191`).
    4. Subnet D: `192.168.1.192/26` (Hosts `192.168.1.193` - `192.168.1.254`, Broadcast `.255`).
* **Observed Result**: Calculations matched lesson solution criteria 100%. No coaching required.
* **Status**: **PASS**

---

## 2. Practice Exercise 2: `cloud-master`
* **Lesson**: *Least Privilege IAM Policies and Roles*
* **Learner Objective**: Author an IAM policy granting read-only access to a specific S3 bucket prefix (`arn:aws:s3:::example-bucket/logs/*`) without leaking broad S3 administrative actions.
* **Contributor Action**:
  - Authored standard JSON statement:
    ```json
    {
      "Version": "2012-10-17",
      "Statement": [
        {
          "Effect": "Allow",
          "Action": ["s3:GetObject", "s3:GetObjectVersion"],
          "Resource": "arn:aws:s3:::example-bucket/logs/*"
        }
      ]
    }
    ```
  - Validated policy syntax using AWS IAM policy simulator reference.
* **Observed Result**: Access strictly bounded to target prefix; wildcard `*` excluded from Action block.
* **Status**: **PASS**

---

## 3. Practice Exercise 3: `docker-engineering`
* **Lesson**: *Writing Efficient Multi-Stage Dockerfiles*
* **Learner Objective**: Create a multi-stage Dockerfile separating build dependencies from the final runtime image.
* **Contributor Action**:
  - Drafted multi-stage Dockerfile:
    ```dockerfile
    FROM golang:1.23-alpine AS builder
    WORKDIR /app
    COPY go.mod go.sum ./
    RUN go mod download
    COPY . .
    RUN CGO_ENABLED=0 GOOS=linux go build -o /app/server .

    FROM alpine:3.20
    RUN adduser -D -u 1000 appuser
    USER appuser
    COPY --from=builder /app/server /usr/local/bin/server
    ENTRYPOINT ["/usr/local/bin/server"]
    ```
  - Verified non-root execution (`USER appuser`) and minimal image payload.
* **Observed Result**: Multi-stage separation eliminates compiler toolchain from production layer.
* **Status**: **PASS**

---

## 4. Practice Exercise 4: `ai-engineer`
* **Lesson**: *Designing Resilient Inference APIs with FastAPI & Pydantic*
* **Learner Objective**: Define a typed Pydantic schema for inference input validation with boundary checks and handle missing fields.
* **Contributor Action**:
  - Implemented typed schema:
    ```python
    from pydantic import BaseModel, Field

    class PredictRequest(BaseModel):
        text: str = Field(..., min_length=1, max_length=1000)
        confidence_threshold: float = Field(0.5, ge=0.0, le=1.0)
    ```
  - Tested negative validation: Empty string and out-of-range floats rejected with HTTP 422 unprocessable entity.
* **Observed Result**: Input validation rejects malformed payloads before invoking model pipeline.
* **Status**: **PASS**

