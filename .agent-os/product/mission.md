# Product Mission

> Last Updated: 2025-01-19
> Version: 1.0.0

## Pitch

Ephemeral Todos is a PHP library that helps developers build todo management systems that focus users on what's important right now by automatically creating and deleting todos based on scheduling rules and completion status, eliminating information overload from traditional persistent todo systems.

## Users

### Primary Customers

- **PHP Developers**: Building todo/task management applications, calendar systems, or productivity tools
- **Product Teams**: Creating user-focused productivity applications that prioritize mental clarity over comprehensive tracking

### User Personas

**Application Developer** (25-45 years old)
- **Role:** Backend/Full-stack Developer
- **Context:** Building productivity apps, calendar systems, or task management tools
- **Pain Points:** Traditional todo systems create overwhelming lists, recurring todos clutter interfaces, completed todos pile up endlessly
- **Goals:** Create clean, focused user experiences that reduce cognitive load, implement smart todo systems that adapt to user behavior

**Product Owner** (30-50 years old)
- **Role:** Product Manager or Startup Founder
- **Context:** Designing productivity applications with user wellness in mind
- **Pain Points:** Users abandon todo apps due to overwhelming interfaces, difficulty balancing comprehensive tracking with usability
- **Goals:** Build applications that genuinely improve user productivity without causing stress, differentiate from traditional todo apps

## The Problem

### Information Overload in Traditional Todo Systems

Traditional todo systems create overwhelming experiences by showing every recurring event stretching infinitely into the future and preserving all past todos regardless of relevance. Users become paralyzed by endless lists and abandon the system entirely.

**Our Solution:** Ephemeral todos appear only when needed and automatically disappear when no longer relevant, keeping users focused on actionable items.

### Persistent Guilt from Missed Tasks

Conventional todo apps maintain permanent records of missed or incomplete tasks, creating psychological burden and guilt that discourages continued use.

**Our Solution:** Automatic deletion rules remove the stigma of missed tasks while preserving the reminder functionality for what matters now.

## Differentiators

### Just-in-Time Todo Creation

Unlike traditional systems that pre-populate future todos, we generate todos precisely when they become actionable. This results in cleaner interfaces and reduced cognitive overhead.

### Smart Automatic Cleanup

Unlike competitors that require manual todo management, our system automatically removes todos based on configurable rules tied to completion status and time elapsed. This results in self-maintaining todo lists that stay relevant.

### Focus on Present Moment

Unlike comprehensive tracking systems, we prioritize current actionability over historical record-keeping. This results in reduced anxiety and increased focus on what can be accomplished today.

## Key Features

### Core Features

- **Fluent Definition API:** Intuitive builder pattern for defining todo scheduling and lifecycle rules
- **Cron-based Scheduling:** Industry-standard cron expressions for flexible recurring todo creation
- **Automatic Deletion Rules:** Configurable cleanup based on completion status and time elapsed
- **Priority Management:** Built-in priority levels (high, medium, low, none) for importance ranking
- **Temporal Logic:** Smart calculation of creation and due dates based on relative time or schedules

### Advanced Features

- **Completion-Aware Cleanup:** Different deletion rules for completed vs incomplete todos
- **Relative Time Handling:** Create todos "2 hours before due date" or "delete 1 week after completion"
- **Just-in-Time Generation:** Generate todo instances only when they become relevant
- **Multiple Deletion Strategies:** Delete after due date, after existing for duration, or based on completion status
- **Content Hashing:** Unique identification for todo instances to prevent duplicates