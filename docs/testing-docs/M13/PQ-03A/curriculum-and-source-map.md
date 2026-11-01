# EUR-68 (PQ-03A) — Technical Curriculum & Source Inventory

**Date**: 2026-11-01  
**Milestone**: M13 — Product quality & content completion  
**Authority**: Linear Issue [EUR-68 (PQ-03A)](https://linear.app/eurusdevsec/issue/EUR-68/pq-03a-complete-the-technical-demo-learning-catalog-with-reproducible)  
**Parent Acceptance Issue**: [EUR-65](https://linear.app/eurusdevsec/issue/EUR-65/m13-acceptance-complete-existing-journeys-curated-learning-content-and)  
**Evaluated Candidate**: `1ce19a23905c0f0b398b24f7e17b9ba3767ce11b` (origin/main)  
**Catalog Allocation**: Group A Technical Curated (4 Communities, 4 Courses, 30 Lessons)

---

## 1. Group A Overview & Structure (AC-01, AC-02)

All Group A technical communities are owned by `creator@scool.local`, set to `ACTIVE` status and `FREE` access mode. Each community contains one comprehensive course structured into logical modules (sections) with step-by-step practical lessons.

| Community Slug | Course Title | Sections | Lessons | Primary Domain / Focus |
|---|---|---|---|---|
| `networking-learning` | Computer Networking Fundamentals | 3 | 6 | OSI/TCP-IP models, packet encapsulation, routing, DNS, Wireshark lab |
| `cloud-master` | Cloud Architecture & DevOps | 4 | 8 | AWS core compute/VPC/IAM, Well-Architected framework, Terraform IaC, S3 reliability |
| `docker-engineering` | Docker Engineering: Containers to Production | 4 | 8 | Dockerfile optimization, multi-stage builds, Compose stacks, non-root security |
| `ai-engineer` | Applied AI Engineering: Data to Reliable Service | 4 | 8 | Data contracts, model evaluation/error slicing, inference API, drift monitoring |

---

## 2. Detailed Module & Lesson Mapping (AC-02, AC-04)

### 2.1 `networking-learning`: Computer Networking Fundamentals (6 Lessons)
* **Target Audience**: Software engineers and system administrators seeking foundational networking fluency.
* **Prerequisites**: Basic terminal command-line familiarity.
* **Sections & Lessons**:
  1. *Section 1: Foundations & The Physical Layer*
     - Lesson 1.1: Packets, Frames, and Encapsulation (Wireshark packet capture analysis).
     - Lesson 1.2: IP Addressing, Subnetting, and CIDR math (IPv4 prefix calculations).
  2. *Section 2: Transport & Routing Protocols*
     - Lesson 2.1: TCP vs UDP: Connection-Oriented vs Connectionless (SYN/ACK handshakes).
     - Lesson 2.2: Routing Protocols & Gateway Operation (BGP, OSPF, and routing table lookup).
  3. *Section 3: Application Layer & Security*
     - Lesson 3.1: DNS Resolution & Troubleshooting (Dig queries, recursive root servers).
     - Lesson 3.2: TLS Handshake & Modern HTTPS Encryption (Cipher suites, certificates).

### 2.2 `cloud-master`: Cloud Architecture & DevOps (8 Lessons)
* **Target Audience**: Cloud engineers and developers building highly available AWS infrastructure.
* **Prerequisites**: Linux basics and basic web architecture understanding.
* **Sections & Lessons**:
  1. *Section 1: Core Compute & Networking*
     - Lesson 1.1: Virtual Private Cloud (VPC) Subnet Isolation & Route Tables.
     - Lesson 1.2: EC2 Instances, Security Groups, and Elastic Load Balancing.
  2. *Section 2: Security & IAM Foundations*
     - Lesson 2.1: Least Privilege IAM Policies, Roles, and Instance Profiles.
     - Lesson 2.2: Encryption at Rest & in Transit using AWS KMS.
  3. *Section 3: Storage & Database Resilience*
     - Lesson 3.1: S3 Bucket Policies, Lifecycle Rules, and Cross-Region Replication.
     - Lesson 3.2: Amazon RDS Multi-AZ Failover & Read Replica Scaling.
  4. *Section 4: Infrastructure as Code & Reliability*
     - Lesson 4.1: Terraform State Management, Remote Backends, and Locking.
     - Lesson 4.2: Designing for High Availability using AWS Well-Architected Framework.

### 2.3 `docker-engineering`: Docker Engineering: Containers to Production (8 Lessons)
* **Target Audience**: DevOps engineers, backend engineers containerizing microservices.
* **Prerequisites**: Command-line usage, Git, basic application deployment.
* **Sections & Lessons**:
  1. *Section 1: Container Foundations*
     - Lesson 1.1: Images, Containers, and Layer Caching Mechanics.
     - Lesson 1.2: Writing Efficient Multi-Stage Dockerfiles.
  2. *Section 2: Local Application Stacks*
     - Lesson 2.1: Persistent Volumes & Container Bridged Networking.
     - Lesson 2.2: Docker Compose Multi-Container Orchestration.
  3. *Section 3: Reliability & Security*
     - Lesson 3.1: Health Checks, PID 1 Signal Handling & Graceful Shutdown.
     - Lesson 3.2: Running Non-Root Users & Container Vulnerability Scanning.
  4. *Section 4: Delivery & Operations*
     - Lesson 4.1: Registry Digest Pinning & Immutable Container Tags.
     - Lesson 4.2: CI Delivery Pipeline Integration & Crashloop Debugging.

### 2.4 `ai-engineer`: Applied AI Engineering: Data to Reliable Service (8 Lessons)
* **Target Audience**: AI engineers and data scientists deploying production ML systems.
* **Prerequisites**: Python proficiency, basic machine learning model concepts.
* **Sections & Lessons**:
  1. *Section 1: Problem & Data Contracts*
     - Lesson 1.1: Defining the ML Task, Decision Boundary, and Value Metrics.
     - Lesson 1.2: Data Contracts, Provenance, and Leakage Prevention.
  2. *Section 2: Model Evaluation & Error Slicing*
     - Lesson 2.1: Establishing Reproducible Benchmarks & Baselines.
     - Lesson 2.2: Systematic Error Slicing & Confusion Matrix Audits.
  3. *Section 3: Serving & Pipeline Verification*
     - Lesson 3.1: Designing Resilient Inference APIs with FastAPI & Pydantic.
     - Lesson 3.2: Golden Dataset Contract Testing & Pipeline Versioning.
  4. *Section 4: Responsible Operations*
     - Lesson 4.1: Monitoring Latency, Data Drift, and Concept Drift.
     - Lesson 4.2: Human-in-the-Loop Review Queues & Privacy Audits.

---

## 3. Media & Cover Asset Provenance (AC-04)

* Cover art for Group A communities is locally generated and stored securely in `database/seeders/assets/community-covers/`.
* Media paths are deterministically managed by `LearningCommunitySeeder` through the private storage disk `Storage::disk('community_media')`.
* All external reference URLs point to official vendor documentation (AWS Well-Architected, Docker Docs, IETF RFCs, FastAPI Guides) without proprietary third-party text or image copyright violations.

