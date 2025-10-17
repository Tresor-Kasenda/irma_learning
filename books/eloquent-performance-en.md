# Mastering Eloquent Optimization with Laravel

## The Complete Guide for High-Performance Applications

---

**By the Community, For the Community**

*Version 1.0 - 2025*

---

## About This Book

Welcome to your journey toward mastering Eloquent optimization! If you develop with Laravel, you've probably already felt this frustration: your application works perfectly in development, but becomes slow once in production with real data.

This book is not just a technical manual - it's a practical guide written by developers, for developers. Every technique presented has been tested in real conditions on applications with millions of users.

### 🎯 What you will learn

- **Diagnose** performance issues in your Laravel applications
- **Master** the most effective Eloquent optimization techniques
- **Implement** intelligent caching solutions
- **Create** Laravel applications capable of handling scale
- **Avoid** the most common pitfalls that slow down applications

### 📚 How to use this book

Each chapter follows a logical progression, but you can also use it as a reference. All code examples are tested and ready to use in your projects.

> 💡 **Tip**: Keep your editor open while reading and test the examples in real time!

---

## Table of Contents

**Part I - The Fundamentals**

1. [Introduction to Eloquent Optimization](#chapter-1)
2. [Understanding Eloquent Performance](#chapter-2)
3. [The N+1 Problem: Your First Enemy](#chapter-3)

**Part II - Essential Techniques**

4. [Mastering Relationship Loading](#chapter-4)
5. [Optimizing Your Queries Like a Pro](#chapter-5)
6. [Pagination and Large Dataset Management](#chapter-6)

**Part III - Advanced Techniques**

7. [Smart Caching with Redis](#chapter-7)
8. [Complex Relationships and Optimization](#chapter-8)
9. [Batch Operations and Chunking](#chapter-9)

**Part IV - In Production**

10. [Performance Monitoring and Debugging](#chapter-10)
11. [Optimization by Application Type](#chapter-11)
12. [Real Case Studies](#chapter-12)

**Part V - Mastery**

13. [Best Practices and Anti-Patterns](#chapter-13)
14. [Automated Performance Testing](#chapter-14)
15. [Production Configuration and Monitoring](#chapter-15)

---

# Chapter 1: Introduction to Eloquent Optimization {#chapter-1}

## Why does this book exist?

Imagine this situation: you've just deployed your beautiful Laravel application. The first users arrive, everything works perfectly. Then, gradually, complaints start coming in: "The application is slow", "Pages take too long to load"...

**Does this story sound familiar?** This is exactly what happened to me during my first Laravel project in production. And it's probably why you're reading this book now.

### The Reality of Laravel Applications

Laravel makes our lives much easier with Eloquent. In a few lines, we can create complex queries:

```php
// So simple to write...
$user = User::find(1);
$posts = $user->posts;
foreach ($posts as $post) {
    echo $post->comments->count() . " comments";
}
```

But what really happens on the database side? If our user has 50 posts, this innocent code generates **51 SQL queries**! One to get the user, one to get their posts, then one query for each post to count its comments.

> 🔍 **Interesting fact**: Most performance issues in Laravel applications come from N+1 queries not detected during development.

### Why Performance Matters

In our current digital world, **every millisecond counts**:

- **40% of users** abandon a site that takes more than 3 seconds to load
- **Google penalizes** slow sites in its search results
- A **100ms** improvement can increase conversions by **1%**

For SaaS applications, it's even more critical. Your users pay for a service, they expect a smooth experience.

### This Book's Approach

This book adopts a **practical and progressive approach**. We start by understanding how Eloquent works, then we learn to diagnose problems, and finally we master optimization techniques.

**Each chapter follows this structure:**

1. **The problem** - Why it's important
2. **The theory** - How it works
3. **The practice** - Concrete examples
4. **The pitfalls** - What to avoid
5. **The recap** - Key points to remember

### Prerequisites

To get the most out of this book, you must have:

- **Intermediate** knowledge of Laravel and PHP
- **Basic** understanding of databases and SQL
- The desire to **learn and experiment**

> 💡 **Tip**: Prepare a test Laravel project to experiment with the book's examples.

### Your First Optimization

Before diving into the details, let's try a simple optimization. Create this code in your test project:

```php
// Test route
Route::get('/test-slow', function () {
    $users = User::limit(10)->get();

    foreach ($users as $user) {
        echo $user->name . " has " . $user->posts->count() . " posts<br>";
    }
});

// Now the optimized version
Route::get('/test-fast', function () {
    $users = User::withCount('posts')->limit(10)->get();

    foreach ($users as $user) {
        echo $user->name . " has " . $user->posts_count . " posts<br>";
    }
});
```

Visit these two routes and observe the performance difference with Laravel Debugbar activated. This is your first Eloquent optimization!

### What Awaits You

In the following chapters, we will explore:

- **How to diagnose** performance issues
- **Optimized loading techniques** for relationships
- **Advanced caching strategies**
- **Specific optimization** according to application type
- **Monitoring tools** in production

**Are you ready to transform your Laravel applications?** Let's go!

---

# Chapter 2: Understanding Eloquent Performance {#chapter-2}

## How Eloquent Works Under the Hood

To optimize effectively, we must first understand **how Eloquent transforms our PHP code into SQL queries**. It's like learning to drive: you can use a car without understanding the engine, but to become a racing driver, you must know every component.

### The Lifecycle of an Eloquent Query

When you write this:

```php
$users = User::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
```

Here's what happens **step by step**:

1. **Query Builder Creation**: Eloquent creates a Query Builder object
2. **Method Chaining**: Each method modifies the builder
3. **SQL Generation**: The builder compiles everything into an SQL query
4. **Execution**: The query is sent to the database
5. **Hydration**: Results are transformed into Eloquent models

```php
// Here's what is actually generated:
// SELECT * FROM users
// WHERE status = 'active'
// ORDER BY created_at DESC
// LIMIT 10
```

### The Magic of Hydration

**Hydration** is the process that transforms raw database data into Eloquent objects. It's convenient, but it has a cost:

```php
// These two approaches retrieve the same data
$users1 = User::select('name', 'email')->get();          // Collection of User models
$users2 = DB::table('users')->select('name', 'email')->get(); // Collection of stdClass objects

// But the first is slower because it must create complete models
```

> ⚡ **Performance Tip**: Use the raw Query Builder for queries where you don't need Eloquent features (accessors, mutators, relations...).

### Relations: Beauty and Performance

Eloquent relations are **beautiful to use**, but can be costly:

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

// This code is elegant but dangerous
$user = User::find(1);
echo $user->profile->bio;           // Query to fetch the profile
echo $user->posts->count();         // Query to fetch the posts
```

Each access to an **unloaded** relation triggers a new database query.

### Let's Experiment: Measuring Impact

Let's create a small laboratory to understand the impact of different approaches:

```php
// Create this Artisan command for your tests
// php artisan make:command PerformanceTest

class PerformanceTest extends Command
{
    protected $signature = 'test:performance';

    public function handle()
    {
        // Test data preparation
        User::factory(100)->create();
        Post::factory(500)->create();

        $this->testDifferentApproaches();
    }

    private function testDifferentApproaches()
    {
        DB::enableQueryLog();

        // Approach 1: Naive
        $start = microtime(true);
        $users = User::limit(10)->get();
        foreach ($users as $user) {
            $postCount = $user->posts->count();
        }
        $time1 = microtime(true) - $start;
        $queries1 = count(DB::getQueryLog());

        // Reset
        DB::disableQueryLog();
        DB::enableQueryLog();

        // Approach 2: Optimized
        $start = microtime(true);
        $users = User::withCount('posts')->limit(10)->get();
        foreach ($users as $user) {
            $postCount = $user->posts_count;
        }
        $time2 = microtime(true) - $start;
        $queries2 = count(DB::getQueryLog());

        // Results
        $this->info("Naive approach: {$time1}s, {$queries1} queries");
        $this->info("Optimized approach: {$time2}s, {$queries2} queries");
        $this->info("Improvement: " . round(($time1 - $time2) / $time1 * 100) . "%");
    }
}
```

**Typical results:**

- Naive approach: 0.25s, 11 queries
- Optimized approach: 0.03s, 1 query
- **Improvement: 88%!**

### The Metrics That Matter

To optimize effectively, you need to monitor these metrics:

#### 1. Number of SQL Queries

```php
// Enable query logging
DB::enableQueryLog();

// Your code here

$queries = DB::getQueryLog();
echo "Number of queries: " . count($queries);
```

#### 2. Execution Time

```php
$start = microtime(true);

// Your code here

$executionTime = microtime(true) - $start;
echo "Execution time: " . round($executionTime * 1000) . "ms";
```

#### 3. Memory Consumption

```php
$startMemory = memory_get_usage();

// Your code here

$memoryUsed = memory_get_usage() - $startMemory;
echo "Memory used: " . round($memoryUsed / 1024 / 1024, 2) . "MB";
```

### The Golden Rule of Performance

> 🏆 **Golden Rule**: A web page should never execute more queries than it displays main entities.

**Concrete examples:**

- **Blog page** with 10 articles → Maximum 3-4 queries (articles + authors + categories)
- **User profile** → Maximum 2-3 queries (user + profile + statistics)
- **Dashboard** with 5 widgets → Maximum 6-7 queries

### Quick Diagnosis: Your Checklist

When your application becomes slow, ask yourself these questions:

**✅ Diagnostic Questions:**

1. How many SQL queries are executed?
2. Are there N+1 queries?
3. Are relations loaded efficiently?
4. Do we use `select()` to limit columns?
5. Is pagination optimized?
6. Is there cache in place?

### Essential Diagnostic Tools

#### Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

Debugbar shows you **in real time**:

- The number of queries executed
- The time of each query
- Duplicate queries
- Memory consumption

#### Laravel Telescope

```bash
php artisan telescope:install
```

Telescope is perfect for **analyzing trends**:

- Slowest queries
- Most used endpoints
- Performance evolution over time

### Your First Performance Audit

Let's create your first audit together on an existing page:

```php
// Create this route in your test application
Route::get('/audit', function () {
    // Enable monitoring
    DB::enableQueryLog();
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    // Your code to audit (example)
    $posts = Post::with('author', 'comments')->paginate(15);

    // Collect metrics
    $executionTime = microtime(true) - $startTime;
    $memoryUsed = memory_get_usage() - $startMemory;
    $queries = DB::getQueryLog();

    // Audit report
    $report = [
        'execution_time' => round($executionTime * 1000) . 'ms',
        'memory_used' => round($memoryUsed / 1024) . 'KB',
        'queries_count' => count($queries),
        'queries' => $queries
    ];

    return response()->json($report, 200, [], JSON_PRETTY_PRINT);
});
```

### Chapter Summary

🎯 **Key Points to Remember:**

1. **Eloquent transforms** your PHP code into SQL queries - understanding this process is essential
2. **Hydration** has a cost - sometimes raw Query Builder is more efficient
3. **Non-optimized relations** are the #1 cause of performance issues
4. **Always measure** before optimizing - intuitions can be misleading
5. **The golden rule**: no more queries than displayed entities

🚀 **Action Items:**

- [ ] Install Laravel Debugbar in your project
- [ ] Test the audit command on one page of your application
- [ ] Identify the 3 slowest pages in your application
- [ ] Note the number of SQL queries for each page

**In the next chapter, we'll tackle the N+1 problem, your first real enemy in Eloquent optimization!**

---

# Chapter 3: The N+1 Problem - Your First Enemy {#chapter-3}

## The Story of the Most Expensive Bug

Let me tell you the true story of an N+1 bug that cost **€50,000** to a startup.

The application worked perfectly with 100 beta users. On public launch day, 10,000 users connected simultaneously. Within 2 hours, the servers were overloaded, the application crashed, and they lost most of their new users.

**The culprit?** A simple loop in the dashboard:

```php
$users = User::limit(100)->get();
foreach ($users as $user) {
    echo $user->profile->avatar; // N+1 query!
}
```

This single line generated **101 SQL queries** per page load. With 1000 simultaneous users, that meant **101,000 queries per second**!

## What is the N+1 Problem?

The N+1 problem occurs when you:

1. **Fetch N entities** (ex: 10 users)
2. **Access a relation** of each entity (ex: their profile)
3. **Generate 1 + N queries** (1 for users + 10 for profiles)

### Anatomy of the Problem

```php
// 📊 This query fetches 10 posts
$posts = Post::limit(10)->get(); // 1 query

// 🔥 This loop generates 10 additional queries!
foreach ($posts as $post) {
    echo $post->author->name;     // 1 query per post
    echo $post->category->name;   // 1 other query per post
}

// Total: 1 + 10 + 10 = 21 queries!
```

### Visualizing the Problem

Let's create an N+1 query detector to see the problem in action:

```php
class N1Detector
{
    private $queries = [];

    public function startDetection()
    {
        $this->queries = [];

        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'time' => $query->time,
                'bindings' => $query->bindings
            ];
        });
    }

    public function analyzeQueries()
    {
        $patterns = [];

        foreach ($this->queries as $query) {
            // Simplify the query to detect patterns
            $pattern = preg_replace('/\d+/', '?', $query['sql']);
            $patterns[$pattern] = ($patterns[$pattern] ?? 0) + 1;
        }

        // Detect repeated queries (probable N+1)
        $suspicious = array_filter($patterns, fn($count) => $count > 2);

        return [
            'total_queries' => count($this->queries),
            'suspicious_patterns' => $suspicious,
            'queries' => $this->queries
        ];
    }
}

// Usage
$detector = new N1Detector();
$detector->startDetection();

// Your suspect code here
$posts = Post::limit(10)->get();
foreach ($posts as $post) {
    echo $post->author->name;
}

$analysis = $detector->analyzeQueries();
dump($analysis);
```

## Solutions to the N+1 Problem

### Solution 1: Eager Loading with `with()`

The most common solution is **eager loading**:

```php
// ❌ BEFORE: 1 + N queries
$posts = Post::limit(10)->get();
foreach ($posts as $post) {
    echo $post->author->name;
}

// ✅ AFTER: Only 2 queries
$posts = Post::with('author')->limit(10)->get();
foreach ($posts as $post) {
    echo $post->author->name; // Data already loaded!
}
```

### Solution 2: Multiple Loading

For multiple relations:

```php
// Loading multiple relations
$posts = Post::with(['author', 'category', 'tags'])->limit(10)->get();

// Loading with conditions
$posts = Post::with(['author:id,name,email'])->limit(10)->get();

// Conditional loading
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', true)
          ->orderBy('created_at', 'desc')
          ->limit(5);
}])->limit(10)->get();
```

### Solution 3: Lazy Eager Loading with `load()`

Sometimes you discover you need a relation **after** fetching your models:

```php
$posts = Post::limit(10)->get();

// Later in the code, you realize you need the authors
if ($needAuthors) {
    $posts->load('author'); // Load authors in one query
}

// Conditional loading
$posts->load(['comments' => function ($query) {
    $query->latest()->limit(3);
}]);
```

## Complex Cases and Advanced Solutions

### Nested Relations

```php
// Loading nested relations
$posts = Post::with([
    'author.profile',           // Author and their profile
    'comments.author',          // Comments and their authors
    'category.parent'           // Category and its parent category
])->get();

// Equivalent to these optimized queries:
// 1. SELECT * FROM posts
// 2. SELECT * FROM users WHERE id IN (1,2,3...)
// 3. SELECT * FROM profiles WHERE user_id IN (1,2,3...)
// 4. SELECT * FROM comments WHERE post_id IN (1,2,3...)
// 5. SELECT * FROM users WHERE id IN (comment author IDs)
// 6. SELECT * FROM categories WHERE id IN (1,2,3...)
// 7. SELECT * FROM categories WHERE id IN (parent category IDs)
```

### Polymorphic Relations

Polymorphic relations require special attention:

```php
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}

// ❌ PROBLEMATIC: N+1 on polymorphic relation
$comments = Comment::limit(10)->get();
foreach ($comments as $comment) {
    echo $comment->commentable->title; // N+1!
}

// ✅ SOLUTION: morphWith
$comments = Comment::with(['commentable' => function (MorphTo $morphTo) {
    $morphTo->morphWith([
        Post::class => ['author:id,name'],
        Video::class => ['channel:id,name'],
    ]);
}])->get();
```

### Counts and Aggregations

```php
// ❌ SLOW: Count in a loop
$users = User::limit(20)->get();
foreach ($users as $user) {
    echo $user->posts->count(); // N+1!
}

// ✅ FAST: withCount
$users = User::withCount('posts')->limit(20)->get();
foreach ($users as $user) {
    echo $user->posts_count;
}

// Conditional counts
$users = User::withCount([
    'posts as published_posts_count' => function ($query) {
        $query->where('published', true);
    }
])->get();
```

## Automatic N+1 Detection

### Using Laravel N+1 Query Detector

```bash
composer require beyondcode/laravel-query-detector --dev
```

This package automatically detects N+1 queries and alerts you:

```php
// In AppServiceProvider
public function boot()
{
    if (app()->environment('local')) {
        \Illuminate\Database\Eloquent\Model::preventLazyLoading();
    }
}
```

### Detection Middleware

Let's create a custom middleware to detect N+1:

```php
class DetectN1Middleware
{
    public function handle($request, Closure $next)
    {
        if (app()->environment(['local', 'testing'])) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        if (app()->environment(['local', 'testing'])) {
            $queries = DB::getQueryLog();

            if (count($queries) > 10) { // Alert threshold
                logger()->warning('Possible N+1 detected', [
                    'url' => $request->url(),
                    'query_count' => count($queries)
                ]);
            }
        }

        return $response;
    }
}
```

## Practical Exercises

### Exercise 1: Basic Detection

Create these models and detect N+1:

```php
// Models
class Author extends Model
{
    public function books() {
        return $this->hasMany(Book::class);
    }
}

class Book extends Model
{
    public function author() {
        return $this->belongsTo(Author::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }
}

// Code to optimize
$books = Book::limit(15)->get();
foreach ($books as $book) {
    echo $book->author->name;
    echo $book->reviews->count();
}
```

**Solution:**

```php
$books = Book::with('author')
            ->withCount('reviews')
            ->limit(15)
            ->get();

foreach ($books as $book) {
    echo $book->author->name;
    echo $book->reviews_count;
}
```

### Exercise 2: Complex Case

Optimize this blog system code:

```php
$posts = Post::where('published', true)->limit(10)->get();

foreach ($posts as $post) {
    echo $post->author->name;
    echo $post->category->name;
    echo $post->tags->pluck('name')->implode(', ');
    echo $post->comments->where('approved', true)->count();
}
```

**Solution:**

```php
$posts = Post::where('published', true)
            ->with([
                'author:id,name',
                'category:id,name',
                'tags:id,name'
            ])
            ->withCount(['comments as approved_comments_count' => function ($query) {
                $query->where('approved', true);
            }])
            ->limit(10)
            ->get();

foreach ($posts as $post) {
    echo $post->author->name;
    echo $post->category->name;
    echo $post->tags->pluck('name')->implode(', ');
    echo $post->approved_comments_count;
}
```

## Advanced Anti-N+1 Strategies

### 1. Conditional Loading in Controllers

```php
class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::published();

        // Conditional loading based on needs
        if ($request->has('include_author')) {
            $query->with('author:id,name');
        }

        if ($request->has('include_stats')) {
            $query->withCount(['comments', 'likes']);
        }

        return $query->paginate(15);
    }
}
```

### 2. Pre-optimized Scopes

```php
class Post extends Model
{
    public function scopeForListing($query)
    {
        return $query->with([
            'author:id,name,avatar',
            'category:id,name,slug'
        ])->withCount('comments');
    }

    public function scopeForSitemap($query)
    {
        return $query->select(['id', 'slug', 'updated_at']);
    }

    public function scopeForApi($query)
    {
        return $query->with([
            'author:id,name',
            'tags:id,name,slug'
        ])->select([
            'id', 'title', 'slug', 'excerpt',
            'user_id', 'published_at'
        ]);
    }
}

// Usage
$posts = Post::forListing()->paginate(15);
```

### 3. Repository Pattern with Optimization

```php
class PostRepository
{
    public function findForDisplay($id)
    {
        return Post::with([
            'author.profile',
            'category',
            'tags',
            'comments' => function ($query) {
                $query->with('author:id,name')
                      ->where('approved', true)
                      ->latest()
                      ->limit(10);
            }
        ])->findOrFail($id);
    }

    public function getForHomepage($limit = 10)
    {
        return Post::with(['author:id,name', 'category:id,name'])
                  ->select(['id', 'title', 'slug', 'excerpt', 'user_id', 'category_id'])
                  ->published()
                  ->latest()
                  ->limit($limit)
                  ->get();
    }
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **The N+1 problem** can ruin your application's performance
2. **`with()`** is your main weapon against N+1
3. **`withCount()`** optimizes aggregation counts
4. **Early detection** prevents production disasters
5. **Pre-optimized scopes** make your code more maintainable

⚠️ **Pitfalls to Avoid:**

- Loading unused relations
- Forgetting nested relations
- Not testing with realistic data
- Ignoring polymorphic queries

🚀 **Action Items:**

- [ ] Install Laravel Query Detector
- [ ] Audit your 5 most important pages
- [ ] Create optimized scopes for your main models
- [ ] Implement N+1 detection in your middleware
- [ ] Test with realistic dataset (1000+ records)

**In the next chapter, we'll master all relationship loading techniques to become true experts!**

---

# Chapter 4: Mastering Relationship Loading {#chapter-4}

## The Art of Optimized Loading

Now that you understand the N+1 problem, it's time to become a master in the art of relationship loading. This chapter will transform how you approach Eloquent relations.

Imagine you're building a house. You could fetch each brick one by one (naive approach), or intelligently plan your deliveries to have all necessary materials at the right time (optimized approach).

## Different Types of Loading

### Eager Loading: The Foundation

**Eager loading** is your basic tool:

```php
// Simple loading
$posts = Post::with('author')->get();

// Multiple loading
$posts = Post::with(['author', 'category', 'tags'])->get();

// Loading with column selection
$posts = Post::with(['author:id,name,email'])->get();
```

> 💡 **Pro Tip**: Always include the primary key and foreign key when using `select()` in relations.

### Lazy Eager Loading: The Catch-up

When you discover late that you need a relation:

```php
$posts = Post::all(); // Relations not loaded

// Later in the code...
if ($displayAuthorInfo) {
    $posts->load('author'); // Load in a single additional query
}

// Loading with conditions
$posts->load(['comments' => function ($query) {
    $query->where('approved', true)->latest();
}]);
```

### Conditional Eager Loading: The Intelligence

Load only what you need:

```php
class PostService
{
    public function getPosts($includeStats = false, $includeComments = false)
    {
        $query = Post::query();

        // Base relations always needed
        $query->with(['author:id,name']);

        // Conditional relations
        if ($includeStats) {
            $query->withCount(['views', 'likes', 'comments']);
        }

        if ($includeComments) {
            $query->with(['comments.author:id,name']);
        }

        return $query->get();
    }
}
```

## Specific Relations and Optimizations

### One-to-One: User Profile

```php
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // Optimized scope for display
    public function scopeWithProfile($query)
    {
        return $query->with(['profile:user_id,bio,avatar,location']);
    }
}

// Optimized usage
$users = User::withProfile()
             ->select(['id', 'name', 'email'])
             ->get();
```

### One-to-Many: Articles and Comments

```php
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function latestComments()
    {
        return $this->hasMany(Comment::class)
                    ->latest()
                    ->limit(5);
    }

    public function approvedComments()
    {
        return $this->hasMany(Comment::class)
                    ->where('approved', true);
    }
}

// Optimized loading based on context
$posts = Post::with([
    'latestComments.author:id,name',    // Latest comments
    'approvedComments' => function ($query) {
        $query->select(['id', 'post_id', 'content', 'created_at']);
    }
])->get();
```

### Many-to-Many: Tags and Pivot

```php
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class)
                    ->withPivot(['created_at', 'created_by'])
                    ->withTimestamps();
    }
}

// Optimization with pivot data
$posts = Post::with(['tags' => function ($query) {
    $query->select(['tags.id', 'name', 'slug'])
          ->orderBy('name');
}])->get();

// Access pivot data
foreach ($posts as $post) {
    foreach ($post->tags as $tag) {
        echo $tag->pivot->created_at;
    }
}
```

### Polymorphic Relations: Universal Comments

```php
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}

// Special optimization for polymorphic relations
$comments = Comment::with(['commentable' => function (MorphTo $morphTo) {
    $morphTo->morphWith([
        Post::class => ['author:id,name'],
        Video::class => ['channel:id,name'],
        Photo::class => ['photographer:id,name'],
    ]);
}])->get();

// Alternative with constrain
$comments = Comment::with(['commentable' => function ($query) {
    $query->select(['id', 'title', 'slug']);
}])->get();
```

## Advanced Loading Techniques

### Has-One-Through and Has-Many-Through

```php
class Country extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}

class User extends Model
{
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Relation through profile
    public function latestPost()
    {
        return $this->hasOneThrough(
            Post::class,
            Profile::class,
            'user_id',
            'user_id',
            'id',
            'user_id'
        )->latest();
    }
}

// Optimized loading
$countries = Country::with([
    'posts' => function ($query) {
        $query->select(['id', 'user_id', 'title', 'created_at'])
              ->latest()
              ->limit(10);
    }
])->get();
```

### Loading with Aggregations

```php
// Count relations
$posts = Post::withCount([
    'comments',
    'likes',
    'comments as approved_comments_count' => function ($query) {
        $query->where('approved', true);
    }
])->get();

// Sums and averages
$users = User::withSum('orders', 'total')
             ->withAvg('orders', 'total')
             ->withMax('orders', 'created_at')
             ->get();

foreach ($users as $user) {
    echo "Total orders: " . $user->orders_sum_total;
    echo "Average: " . $user->orders_avg_total;
    echo "Last order: " . $user->orders_max_created_at;
}
```

### Relation Existence

```php
// Check existence without loading data
$users = User::withExists([
    'posts as has_posts',
    'comments as has_comments'
])->get();

foreach ($users as $user) {
    if ($user->has_posts) {
        echo "This user has posts";
    }
}

// Combine existence + count
$users = User::withExists('posts')
             ->withCount('posts')
             ->get();
```

## Contextual Optimizations

### Loading for APIs

```php
class PostApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::select([
            'id', 'title', 'slug', 'excerpt',
            'user_id', 'category_id', 'published_at'
        ]);

        // Loading based on API parameters
        $includes = explode(',', $request->get('include', ''));

        if (in_array('author', $includes)) {
            $query->with('author:id,name,avatar');
        }

        if (in_array('category', $includes)) {
            $query->with('category:id,name,slug');
        }

        if (in_array('stats', $includes)) {
            $query->withCount(['comments', 'likes']);
        }

        return $query->paginate(15);
    }
}

// Usage: GET /api/posts?include=author,stats
```

### Loading for Views

```php
// In your controller
class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::with([
            'author:id,name,avatar',
            'category:id,name,slug,color',
            'tags:id,name,slug'
        ])
        ->withCount('comments')
        ->select([
            'id', 'title', 'slug', 'excerpt', 'featured_image',
            'user_id', 'category_id', 'published_at', 'reading_time'
        ])
        ->published()
        ->latest('published_at')
        ->paginate(12);

        return view('blog.index', compact('posts'));
    }

    public function show(Post $post)
    {
        $post->load([
            'author.profile',
            'category',
            'tags',
            'comments' => function ($query) {
                $query->with('author:id,name,avatar')
                      ->where('approved', true)
                      ->latest()
                      ->limit(20);
            }
        ]);

        // Similar views (avoid N+1)
        $relatedPosts = Post::where('category_id', $post->category_id)
                           ->where('id', '!=', $post->id)
                           ->with(['author:id,name'])
                           ->select(['id', 'title', 'slug', 'user_id'])
                           ->limit(5)
                           ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }
}
```

## Cache Strategies for Relations

### Caching Frequent Relations

```php
class Post extends Model
{
    public function getCachedAuthor()
    {
        return Cache::remember(
            "post.{$this->id}.author",
            3600,
            fn() => $this->author
        );
    }

    public function getCachedTags()
    {
        return Cache::remember(
            "post.{$this->id}.tags",
            7200,
            fn() => $this->tags()->orderBy('name')->get()
        );
    }
}

// Cache invalidation
class PostObserver
{
    public function updated(Post $post)
    {
        Cache::forget("post.{$post->id}.author");
        Cache::forget("post.{$post->id}.tags");
    }
}
```

### Repository Pattern with Cache

```php
class PostRepository
{
    public function findWithRelations($id, $relations = [])
    {
        $cacheKey = "post.{$id}." . implode('.', $relations);

        return Cache::remember($cacheKey, 3600, function () use ($id, $relations) {
            return Post::with($relations)->findOrFail($id);
        });
    }

    public function getPopularWithAuthors($limit = 10)
    {
        return Cache::remember('posts.popular.with.authors', 1800, function () use ($limit) {
            return Post::with(['author:id,name,avatar'])
                      ->withCount('views')
                      ->orderBy('views_count', 'desc')
                      ->limit($limit)
                      ->get();
        });
    }
}
```

## Advanced Use Cases

### E-commerce: Products with Variants

```php
class Product extends Model
{
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function availableVariants()
    {
        return $this->variants()->where('stock', '>', 0);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)
                    ->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)
                    ->where('is_primary', true);
    }
}

// Optimized loading for catalog
$products = Product::with([
    'primaryImage:product_id,url,alt_text',
    'availableVariants:id,product_id,name,price,stock',
    'category:id,name,slug'
])
->select([
    'id', 'name', 'slug', 'base_price', 'category_id'
])
->paginate(24);
```

### Nested Comments System

```php
class Comment extends Model
{
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Optimized recursive loading
    public function scopeWithAllReplies($query, $depth = 3)
    {
        $with = ['author:id,name,avatar'];

        for ($i = 1; $i <= $depth; $i++) {
            $with[] = str_repeat('replies.', $i) . 'author:id,name,avatar';
        }

        return $query->with($with);
    }
}

// Usage
$comments = Comment::whereNull('parent_id')
                  ->withAllReplies(2) // 2 levels of replies
                  ->get();
```

## Testing and Validation of Optimizations

### Unit Tests for Relations

```php
// tests/Unit/PostRelationTest.php
class PostRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_with_author_uses_optimal_queries()
    {
        Post::factory(10)->create();

        DB::enableQueryLog();

        $posts = Post::with('author')->get();
        $posts->each(fn($post) => $post->author->name);

        $queryCount = count(DB::getQueryLog());

        // Should use exactly 2 queries
        $this->assertEquals(2, $queryCount);
    }

    public function test_post_with_comments_count_is_efficient()
    {
        $post = Post::factory()->create();
        Comment::factory(20)->for($post)->create();

        DB::enableQueryLog();

        $postWithCount = Post::withCount('comments')->find($post->id);
        $commentCount = $postWithCount->comments_count;

        $queryCount = count(DB::getQueryLog());

        // Single query with JOIN/subquery
        $this->assertEquals(1, $queryCount);
        $this->assertEquals(20, $commentCount);
    }
}
```

### Integration Test

```php
public function test_blog_page_loads_efficiently()
{
    User::factory(5)->create();
    Category::factory(3)->create();
    Post::factory(15)->create();
    Comment::factory(50)->create();

    DB::enableQueryLog();

    $response = $this->get('/blog');

    $queryCount = count(DB::getQueryLog());

    $response->assertOk();

    // Blog page should not exceed 5 queries
    $this->assertLessThanOrEqual(5, $queryCount);
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Eager loading** (`with()`) is your main tool
2. **Conditional loading** avoids loading unnecessary data
3. **Aggregations** (`withCount`, `withSum`) are more efficient than loops
4. **Polymorphic relations** require `morphWith()` to be optimized
5. **Relation caching** can significantly improve performance

🛠️ **Techniques Mastered:**

- Simple and multiple Eager Loading
- Lazy Eager Loading for late optimization
- Intelligent conditional loading
- Optimized polymorphic relations
- Efficient aggregations and counts
- Advanced caching strategies

⚠️ **Pitfalls to Avoid:**

- Forgetting primary/foreign keys in `select()`
- Loading too many unused relations
- Neglecting deep nested relations
- Ignoring validation through tests

🚀 **Action Items:**

- [ ] Audit your main models to identify critical relations
- [ ] Create optimized scopes for each usage context
- [ ] Implement tests to validate your optimizations
- [ ] Set up caching strategies for frequent relations
- [ ] Document your loading patterns for your team

**In the next chapter, we'll discover how to optimize your SQL queries like a true pro!**

---

# Chapter 5: Optimizing Your Queries Like a Pro {#chapter-5}

## The Difference Between a Developer and an Expert

A junior developer writes `User::all()` and prays it works. A senior developer writes `User::select(['id', 'name'])->limit(100)->get()` and knows why.

In this chapter, we're going to transform you into an Eloquent query expert. You'll learn to think like a database and write queries that make your MySQL server sing.

## The Art of Intelligent SELECT

### Why `SELECT *` is Your Enemy

```php
// ❌ This query can load unnecessary megabytes
$users = User::all();

// Each user might have:
// - A 2000-character biography
// - Complex JSON preferences
// - Extended metadata
// = Potentially 50KB per user!
```

**Real calculation:** 1000 users × 50KB = **50MB of transferred data** when you only needed names!

### The Solution: Targeted SELECT

```php
// ✅ Only necessary data
$users = User::select(['id', 'name', 'email'])->get();

// For a simple list: 1000 users × 100 bytes = 100KB!
// Gain: 99.8% reduction!
```

### Advanced Selection Techniques

```php
// Selection with aliases for clarity
$stats = User::select([
    'id',
    'name',
    DB::raw('CONCAT(first_name, " ", last_name) as full_name'),
    DB::raw('DATEDIFF(NOW(), created_at) as days_since_registration')
])->get();

// Conditional selection
class UserRepository
{
    public function getUsers($includeProfile = false)
    {
        $columns = ['id', 'name', 'email'];

        if ($includeProfile) {
            $columns = array_merge($columns, ['bio', 'avatar', 'location']);
        }

        return User::select($columns)->get();
    }
}
```

## Mastering Query Scopes

### Basic Scopes: Your Foundation

```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('published', true)
                    ->where('published_at', '<=', now());
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('user_id', $authorId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

// Elegant and reusable usage
$recentPosts = Post::published()->recent(7)->get();
$authorPosts = Post::published()->byAuthor(5)->get();
```

### Complex Scopes: Maximum Power

```php
class Post extends Model
{
    public function scopeWithEngagementStats($query)
    {
        return $query->select([
            'posts.*',
            DB::raw('(
                SELECT COUNT(*) FROM comments
                WHERE comments.post_id = posts.id
                AND comments.approved = 1
            ) as comments_count'),
            DB::raw('(
                SELECT COUNT(*) FROM likes
                WHERE likes.post_id = posts.id
            ) as likes_count'),
            DB::raw('(comments_count + likes_count * 2) as engagement_score')
        ]);
    }

    public function scopePopularInPeriod($query, $startDate, $endDate)
    {
        return $query->withEngagementStats()
                    ->whereBetween('published_at', [$startDate, $endDate])
                    ->orderBy('engagement_score', 'desc');
    }

    // Scope to optimize searches
    public function scopeSearchOptimized($query, $term)
    {
        return $query->select(['id', 'title', 'excerpt', 'slug'])
                    ->where(function ($q) use ($term) {
                        $q->where('title', 'LIKE', "%{$term}%")
                          ->orWhere('excerpt', 'LIKE', "%{$term}%")
                          ->orWhere('content', 'LIKE', "%{$term}%");
                    })
                    ->orderByRaw("
                        CASE
                        WHEN title LIKE ? THEN 1
                        WHEN excerpt LIKE ? THEN 2
                        ELSE 3
                        END
                    ", ["%{$term}%", "%{$term}%"]);
    }
}

// Usage
$popularPosts = Post::popularInPeriod(
    now()->subDays(30),
    now()
)->limit(10)->get();

$searchResults = Post::searchOptimized('Laravel')->paginate(15);
```

## Pluck vs Get: Choosing the Right Weapon

### When to Use Pluck

```php
// ✅ To retrieve only one column
$userEmails = User::pluck('email'); // Returns Collection<string>

// ✅ To create associative arrays
$userList = User::pluck('name', 'id'); // [1 => 'John', 2 => 'Jane']

// ✅ With conditions
$adminEmails = User::where('role', 'admin')->pluck('email');

// ✅ Calculated columns
$fullNames = User::pluck(DB::raw("CONCAT(first_name, ' ', last_name)"));
```

### Performance Comparison

```php
// Performance test: retrieve 10,000 user emails

// ❌ SLOW: Get then extraction
$startTime = microtime(true);
$emails1 = User::select(['email'])->get()->pluck('email');
$time1 = microtime(true) - $startTime;

// ✅ FAST: Direct pluck
$startTime = microtime(true);
$emails2 = User::pluck('email');
$time2 = microtime(true) - $startTime;

// Typical result:
// Get then pluck: 150ms + 50MB memory
// Direct pluck: 45ms + 15MB memory
// Gain: 70% faster, 67% less memory!
```

## Raw Queries: When Eloquent Reaches its Limits

### Recognizing Use Cases

Use raw queries when you need:

- **Advanced SQL functions** (window functions, CTEs)
- **Maximum performance** for complex queries
- **Sophisticated aggregation queries**

### Practical Examples

#### Complex Data Analysis

```php
// Advanced monthly statistics
$monthlyStats = DB::select("
    SELECT
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as total_posts,
        COUNT(DISTINCT user_id) as unique_authors,
        AVG(reading_time) as avg_reading_time,
        SUM(views) as total_views,
        RANK() OVER (ORDER BY COUNT(*) DESC) as month_rank
    FROM posts
    WHERE published = 1
      AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");

// Convert to Collection to use Laravel helpers
$monthlyStats = collect($monthlyStats);
```

#### Reporting Queries

```php
class ReportService
{
    public function getUserEngagementReport($userId, $period = 30)
    {
        return DB::select("
            WITH user_activity AS (
                SELECT
                    DATE(created_at) as activity_date,
                    'post' as activity_type,
                    1 as activity_count
                FROM posts
                WHERE user_id = ?
                  AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)

                UNION ALL

                SELECT
                    DATE(created_at) as activity_date,
                    'comment' as activity_type,
                    1 as activity_count
                FROM comments
                WHERE user_id = ?
                  AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            )
            SELECT
                activity_date,
                SUM(CASE WHEN activity_type = 'post' THEN activity_count ELSE 0 END) as posts_count,
                SUM(CASE WHEN activity_type = 'comment' THEN activity_count ELSE 0 END) as comments_count,
                COUNT(DISTINCT activity_type) as activity_types
            FROM user_activity
            GROUP BY activity_date
            ORDER BY activity_date DESC
        ", [$userId, $period, $userId, $period]);
    }
}
```

### Hybrid Approach: Best of Both Worlds

```php
class PostRepository
{
    public function getTopAuthorsWithStats($limit = 10)
    {
        // Complex query in pure SQL for performance
        $topAuthors = DB::select("
            SELECT
                u.id,
                u.name,
                u.email,
                COUNT(p.id) as posts_count,
                AVG(p.views) as avg_views,
                MAX(p.created_at) as last_post_date
            FROM users u
            INNER JOIN posts p ON u.id = p.user_id
            WHERE p.published = 1
              AND p.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            GROUP BY u.id, u.name, u.email
            HAVING posts_count >= 5
            ORDER BY avg_views DESC
            LIMIT ?
        ", [$limit]);

        // Convert to Eloquent models to use relations
        $userIds = collect($topAuthors)->pluck('id');
        $users = User::whereIn('id', $userIds)
                    ->with(['profile', 'socialLinks'])
                    ->get()
                    ->keyBy('id');

        // Merge data
        return collect($topAuthors)->map(function ($author) use ($users) {
            $user = $users->get($author->id);
            return (object) array_merge(
                (array) $author,
                ['profile' => $user->profile, 'social_links' => $user->socialLinks]
            );
        });
    }
}
```

## WHERE Condition Optimization

### Index-Friendly Queries

```php
// ✅ Uses index on 'status'
$activeUsers = User::where('status', 'active')->get();

// ❌ Doesn't use index efficiently
$users = User::where(DB::raw('UPPER(name)'), 'JOHN')->get();

// ✅ Better: store in lowercase or use functional index
$users = User::where('name_lowercase', 'john')->get();

// ✅ Uses composite index (status, created_at)
$recentActive = User::where('status', 'active')
                   ->where('created_at', '>=', now()->subDays(30))
                   ->get();
```

### Optimized Dynamic Conditions

```php
class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::select(['id', 'title', 'slug', 'published_at', 'user_id']);

        // Efficient status filtering
        if ($request->has('status')) {
            $query->where('published', $request->status === 'published');
        }

        // Optimized search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Date filter with index
        if ($request->filled('date_from')) {
            $query->where('published_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('published_at', '<=', $request->date_to);
        }

        // Optimized sorting
        $sortColumn = $request->get('sort', 'published_at');
        $sortDirection = $request->get('direction', 'desc');

        if (in_array($sortColumn, ['title', 'published_at', 'created_at'])) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query->with(['author:id,name'])->paginate(15);
    }
}
```

## JOIN Optimization with Eloquent

### Explicit JOINs vs Relations

```php
// ❌ Can generate suboptimal queries
$posts = Post::with('author')
            ->whereHas('author', function ($query) {
                $query->where('verified', true);
            })
            ->get();

// ✅ More efficient explicit JOIN
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
            ->where('users.verified', true)
            ->select([
                'posts.*',
                'users.name as author_name',
                'users.email as author_email'
            ])
            ->get();

// ✅ Even better: with optimized Query Builder
$posts = DB::table('posts')
          ->join('users', 'posts.user_id', '=', 'users.id')
          ->where('users.verified', true)
          ->select([
              'posts.id', 'posts.title', 'posts.slug',
              'users.name as author_name'
          ])
          ->get();
```

### LEFT JOIN for Optional Relations

```php
// Posts with or without category
$posts = Post::leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->select([
                'posts.*',
                'categories.name as category_name'
            ])
            ->get();

// With conditions on optional relation
$posts = Post::leftJoin('categories', 'posts.category_id', '=', 'categories.id')
            ->where(function ($query) {
                $query->whereNull('categories.id')
                      ->orWhere('categories.active', true);
            })
            ->select(['posts.*', 'categories.name as category_name'])
            ->get();
```

## Advanced Pagination Techniques

### Cursor Pagination: The Solution for Large Volumes

```php
// ❌ SLOW: Classic pagination on large table
$posts = Post::orderBy('id')->paginate(15, ['*'], 'page', 10000);
// This query becomes very slow after page 1000

// ✅ FAST: Cursor pagination
$posts = Post::orderBy('id')->cursorPaginate(15);

// Cursor pagination with custom sorting
$posts = Post::orderBy('published_at', 'desc')
            ->orderBy('id', 'desc') // Tie-breaker for uniqueness
            ->cursorPaginate(15);
```

### Optimized Pagination with Count

```php
class OptimizedPaginator
{
    public static function paginateWithoutCount($query, $perPage = 15)
    {
        $items = $query->take($perPage + 1)->get();

        $hasMore = $items->count() > $perPage;
        if ($hasMore) {
            $items->pop(); // Remove extra item
        }

        return [
            'data' => $items,
            'has_more' => $hasMore,
            'per_page' => $perPage
        ];
    }
}

// Usage: avoids expensive COUNT() query
$result = OptimizedPaginator::paginateWithoutCount(
    Post::with('author')->latest(),
    15
);
```

## Practical Cases: E-commerce and SaaS

### Optimized Product Catalog

```php
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::select([
            'id', 'name', 'slug', 'price', 'discount_price',
            'category_id', 'brand_id', 'stock_quantity'
        ]);

        // Filters with index
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('brand')) {
            $query->whereIn('brand_id', (array) $request->brand);
        }

        // Optimized price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Available stock only
        if ($request->boolean('in_stock_only')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Optimized relation loading
        $query->with([
            'category:id,name,slug',
            'brand:id,name,logo',
            'images' => function ($q) {
                $q->where('is_primary', true)
                  ->select(['product_id', 'url', 'alt_text']);
            }
        ]);

        // Sorting with index
        $sortOptions = [
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'name' => ['name', 'asc'],
            'newest' => ['created_at', 'desc']
        ];

        $sort = $request->get('sort', 'newest');
        if (isset($sortOptions[$sort])) {
            [$column, $direction] = $sortOptions[$sort];
            $query->orderBy($column, $direction);
        }

        return $query->paginate(24);
    }
}
```

### Multi-tenant SaaS Dashboard

```php
class DashboardService
{
    public function getDashboardData($tenantId, $period = 30)
    {
        // Single query for all statistics
        $stats = DB::select("
            SELECT
                'users' as metric,
                COUNT(*) as current_count,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN 1 END) as period_count,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count
            FROM users WHERE tenant_id = ?

            UNION ALL

            SELECT
                'orders' as metric,
                COUNT(*) as current_count,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN 1 END) as period_count,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count
            FROM orders WHERE tenant_id = ?

            UNION ALL

            SELECT
                'revenue' as metric,
                COALESCE(SUM(total), 0) as current_count,
                COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) THEN total ELSE 0 END), 0) as period_count,
                COALESCE(SUM(CASE WHEN DATE(created_at) = CURDATE() THEN total ELSE 0 END), 0) as today_count
            FROM orders WHERE tenant_id = ? AND status = 'completed'
        ", [$period, $tenantId, $period, $tenantId, $period, $tenantId]);

        // Transform to usable format
        return collect($stats)->mapWithKeys(function ($stat) {
            return [$stat->metric => [
                'current' => $stat->current_count,
                'period' => $stat->period_count,
                'today' => $stat->today_count
            ]];
        });
    }
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Targeted SELECT** can reduce data transfer by 99%
2. **Query Scopes** make your queries reusable and maintainable
3. **pluck()** is 70% faster than get()->pluck() for single columns
4. **Raw queries** are necessary for complex cases
5. **Cursor pagination** solves performance issues on large tables

🛠️ **Techniques Mastered:**

- Intelligent column selection
- Creating complex and reusable scopes
- WHERE condition optimization with indexes
- Explicit JOINs for better performance
- Advanced pagination techniques
- Hybrid SQL + Eloquent queries

⚡ **Critical Optimizations:**

- **Avoid `SELECT *`** - always specify necessary columns
- **Use indexes** - write your conditions to exploit them
- **Prefer pluck()** to retrieve a single column
- **Combine SQL and Eloquent** as needed

🚀 **Action Items:**

- [ ] Audit your most frequent queries with EXPLAIN
- [ ] Replace `SELECT *` with targeted selections
- [ ] Create optimized scopes for your main use cases
- [ ] Implement cursor pagination on your large tables
- [ ] Test performance before/after optimization

**In the next chapter, we'll master pagination and large dataset management!**

---

# Chapter 6: Pagination and Large Dataset Management {#chapter-6}

## The Day Netflix Learned to Handle 100 Million Users

In 2012, Netflix had a problem: their recommendation system was crashing regularly. The cause? They were trying to load **all movies** of a user at once to calculate recommendations. Some users had watched over 10,000 movies!

The solution? **Smart pagination and chunking**. Instead of loading everything, they learned to process data in small blocks. Result: 99% reduction in memory consumption and a system that could handle the load.

This lesson applies perfectly to our Laravel applications.

## Different Types of Pagination

### Classic Pagination: Simple but Limited

```php
// ✅ Perfect for small to medium collections
$posts = Post::paginate(15);

// In your view:
{{ $posts->links() }}

// Advantages:
// - Familiar interface for users
// - Navigation by page number
// - Total element counter

// ❌ Problems with large tables:
// - OFFSET becomes very slow after page 1000
// - Total count (COUNT) expensive
// - Consistency issues if data changes
```

### Simple Pagination: Faster

```php
// ✅ Faster because it avoids COUNT()
$posts = Post::simplePaginate(15);

// Generates only "Previous" and "Next"
// Perfect for feeds or timeline-type flows
```

### Cursor Pagination: The High Performance Solution

```php
// 🚀 Constant performance even on millions of records
$posts = Post::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // Important tie-breaker!
            ->cursorPaginate(15);

// Cursor pagination uses column values
// instead of OFFSET, which stays fast regardless of the "page"
```

## Deep Dive: Why Classic Pagination Becomes Slow

### The OFFSET Problem

```sql
-- Page 1: FAST
SELECT *
FROM posts
ORDER BY created_at DESC LIMIT 15
OFFSET 0;

-- Page 1000: SLOW!
SELECT *
FROM posts
ORDER BY created_at DESC LIMIT 15
OFFSET 14985;

-- MySQL must:
-- 1. Sort ALL records
-- 2. Skip the first 14,985
-- 3. Return the next 15
```

### Demonstration with Real Data

Let's create a performance test to visualize the problem:

```php
// Artisan command to test pagination
class PaginationBenchmark extends Command
{
    protected $signature = 'test:pagination';

    public function handle()
    {
        // Creating test data
        $this->info('Creating 100,000 posts...');
        Post::factory(100000)->create();

        $this->testPaginationPerformance();
    }

    private function testPaginationPerformance()
    {
        $pages = [1, 100, 1000, 5000];

        foreach ($pages as $page) {
            $this->info("Testing page {$page}");

            // Classic pagination
            $start = microtime(true);
            $posts = Post::paginate(15, ['*'], 'page', $page);
            $classicTime = microtime(true) - $start;

            // Cursor pagination (simulation)
            $start = microtime(true);
            $posts = Post::orderBy('id', 'desc')->cursorPaginate(15);
            $cursorTime = microtime(true) - $start;

            $this->info("  Classic: " . round($classicTime * 1000) . "ms");
            $this->info("  Cursor: " . round($cursorTime * 1000) . "ms");
            $this->info("  Gain: " . round((($classicTime - $cursorTime) / $classicTime) * 100) . "%");
            $this->info('---');
        }
    }
}
```

**Typical results:**

- **Page 1**: Classic 50ms, Cursor 45ms (10% gain)
- **Page 100**: Classic 150ms, Cursor 45ms (70% gain)
- **Page 1000**: Classic 800ms, Cursor 45ms (94% gain)
- **Page 5000**: Classic 3500ms, Cursor 45ms (99% gain)

## Mastering Cursor Pagination

### Basic Implementation

```php
class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['author:id,name'])
                    ->select(['id', 'title', 'slug', 'user_id', 'created_at'])
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc') // Crucial tie-breaker
                    ->cursorPaginate(15);

        return response()->json([
            'data' => $posts->items(),
            'next_cursor' => $posts->nextCursor()?->encode(),
            'prev_cursor' => $posts->previousCursor()?->encode(),
            'has_more' => $posts->hasMorePages()
        ]);
    }
}
```

### Cursor Pagination with Custom Sorting

```php
// Sort by popularity (calculated score)
$posts = Post::select([
        'id', 'title', 'slug',
        DB::raw('(views * 0.1 + likes * 0.3 + comments * 0.6) as popularity_score')
    ])
    ->orderBy('popularity_score', 'desc')
    ->orderBy('id', 'desc') // Tie-breaker
    ->cursorPaginate(20);

// Sort by date with filters
$posts = Post::where('published', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate(15);
```

### API Cursor with Dynamic Filters

```php
class ApiPostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::select(['id', 'title', 'slug', 'created_at']);

        // Filters (maintain cursor consistency)
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('author')) {
            $query->where('user_id', $request->author);
        }

        // Important: consistent sorting for cursor
        $query->orderBy('created_at', 'desc')
              ->orderBy('id', 'desc');

        $posts = $query->cursorPaginate(15);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'next_cursor' => $posts->nextCursor()?->encode(),
                'prev_cursor' => $posts->previousCursor()?->encode(),
                'has_more_pages' => $posts->hasMorePages(),
                'per_page' => $posts->perPage()
            ]
        ]);
    }
}
```

## Chunking: Processing Large Datasets

### The Memory Problem

```php
// ❌ DANGER: Can consume several GB of RAM
$users = User::all(); // 1 million users = server crash!

foreach ($users as $user) {
    $user->calculateLoyaltyPoints();
}
```

### Solution: Chunking

```php
// ✅ Constant memory, processing in blocks
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        $user->calculateLoyaltyPoints();
        $user->save();
    }
});

// With progress bar
$totalUsers = User::count();
$processed = 0;

User::chunk(1000, function ($users) use (&$processed, $totalUsers) {
    foreach ($users as $user) {
        $user->calculateLoyaltyPoints();
        $user->save();
        $processed++;
    }

    $percentage = round(($processed / $totalUsers) * 100);
    echo "Progress: {$percentage}%\n";
});
```

### ChunkById: Avoiding Duplicates

```php
// ❌ Problem: if records are modified during chunking
User::orderBy('created_at')->chunk(1000, function ($users) {
    foreach ($users as $user) {
        $user->update(['processed' => true]); // Changes the order!
    }
});

// ✅ Solution: chunkById maintains consistency
User::chunkById(1000, function ($users) {
    foreach ($users as $user) {
        $user->update(['processed' => true]); // No problem!
    }
}, 'id'); // Uses ID as stable reference
```

### Advanced Chunking with Error Control

```php
class DataProcessor
{
    public function processUsers($chunkSize = 1000)
    {
        $totalProcessed = 0;
        $errors = [];

        try {
            User::chunkById($chunkSize, function ($users) use (&$totalProcessed, &$errors) {
                DB::beginTransaction();

                try {
                    foreach ($users as $user) {
                        $this->processUser($user);
                        $totalProcessed++;
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollback();

                    $errors[] = [
                        'chunk_start_id' => $users->first()->id,
                        'error' => $e->getMessage()
                    ];

                    // Optional: process individually on error
                    $this->processSingleUsers($users, $totalProcessed, $errors);
                }
            });

        } catch (\Exception $e) {
            logger()->error('Processing failed', ['error' => $e->getMessage()]);
        }

        return [
            'processed' => $totalProcessed,
            'errors' => $errors
        ];
    }

    private function processSingleUsers($users, &$totalProcessed, &$errors)
    {
        foreach ($users as $user) {
            try {
                DB::transaction(function () use ($user) {
                    $this->processUser($user);
                });
                $totalProcessed++;
            } catch (\Exception $e) {
                $errors[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ];
            }
        }
    }
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Individual operations** are 100x slower than batch operations
2. **Intelligent chunking** with error handling prevents timeouts and crashes
3. **Bulk operations** (INSERT, UPDATE, UPSERT) are essential for large volumes
4. **Batch queue jobs** enable parallel and asynchronous processing
5. **Progress tracking** improves user experience and monitoring

🛠️ **Mastered Techniques:**

- Safe chunking with error handling and rollback
- Optimized bulk insert, update and upsert
- Parallel processing with Laravel Batches
- High-performance data migration
- Automated database cleanup

⚡ **Typical Performance Gains:**

- **Bulk INSERT**: 50x faster than individual INSERTs
- **Optimized chunking**: 10x reduction in processing time
- **Queue batches**: Parallel processing according to available workers
- **Progress tracking**: Better visibility without performance impact

🚀 **Action Items:**

- [ ] Identify your current mass operations
- [ ] Implement chunking on large datasets
- [ ] Replace individual operations with bulk
- [ ] Configure queue batches for asynchronous processing
- [ ] Add progress tracking on long operations

**In the next chapter, we'll explore advanced performance monitoring and debugging!**

---

```php
class ProgressiveChunkProcessor
{
    protected $totalItems;
    protected $processed = 0;
    protected $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function processWithProgress($query, callable $processor, $chunkSize = 1000)
    {
        $this->totalItems = $query->count();
        $this->info("Starting processing of {$this->totalItems} items...");

        $query->chunkById($chunkSize, function ($chunk) use ($processor) {
            $this->processChunkWithProgress($chunk, $processor);
        });

        $this->showFinalStats();
    }

    private function processChunkWithProgress($chunk, $processor)
    {
        foreach ($chunk as $item) {
            $processor($item);
            $this->processed++;

            // Show progress every 100 items
            if ($this->processed % 100 === 0) {
                $this->showProgress();
            }
        }
    }

    private function showProgress()
    {
        $percentage = round(($this->processed / $this->totalItems) * 100, 1);
        $elapsed = round(microtime(true) - $this->startTime, 2);
        $itemsPerSecond = round($this->processed / $elapsed, 1);

        $remaining = $this->totalItems - $this->processed;
        $eta = $itemsPerSecond > 0 ? round($remaining / $itemsPerSecond) : 0;

        echo "Progress: {$percentage}% ({$this->processed}/{$this->totalItems}) - ";
        echo "{$itemsPerSecond} items/sec - ETA: {$eta}s\n";
    }

    private function showFinalStats()
    {
        $totalTime = round(microtime(true) - $this->startTime, 2);
        $avgSpeed = round($this->totalItems / $totalTime, 1);

        echo "\nCompleted! Processed {$this->totalItems} items in {$totalTime}s ({$avgSpeed} items/sec)\n";
    }
}

// Artisan command with progress
class ProcessUsersCommand extends Command
{
    protected $signature = 'users:process {--chunk=1000}';

    public function handle()
    {
        $processor = new ProgressiveChunkProcessor();

        $processor->processWithProgress(
            User::where('status', 'pending'),
            function ($user) {
                $user->processAccount();
            },
            $this->option('chunk')
        );
    }
}
```

## Advanced Bulk Operations

### Optimized Bulk Insert

```php
class BulkInsertService
{
    public function bulkInsert($table, array $data, $chunkSize = 1000)
    {
        if (empty($data)) {
            return 0;
        }

        $chunks = array_chunk($data, $chunkSize);
        $inserted = 0;

        foreach ($chunks as $chunk) {
            $inserted += DB::table($table)->insert($chunk);
        }

        return $inserted;
    }

    public function bulkUpsert($table, array $data, $uniqueColumns, $updateColumns, $chunkSize = 1000)
    {
        if (empty($data)) {
            return 0;
        }

        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            // Prepare columns for ON DUPLICATE KEY UPDATE
            $updateClause = collect($updateColumns)
                ->map(fn($column) => "`{$column}` = VALUES(`{$column}`)")
                ->implode(', ');

            $columns = array_keys($chunk[0]);
            $columnsString = '`' . implode('`, `', $columns) . '`';

            $values = [];
            foreach ($chunk as $row) {
                $rowValues = collect($row)
                    ->map(fn($value) => is_null($value) ? 'NULL' : "'" . addslashes($value) . "'")
                    ->implode(', ');
                $values[] = "({$rowValues})";
            }

            $valuesString = implode(', ', $values);

            $sql = "INSERT INTO `{$table}` ({$columnsString}) VALUES {$valuesString}";

            if (!empty($updateColumns)) {
                $sql .= " ON DUPLICATE KEY UPDATE {$updateClause}";
            }

            DB::statement($sql);
        }
    }

    public function bulkUpdate($table, array $data, $primaryKey = 'id', $chunkSize = 500)
    {
        if (empty($data)) {
            return 0;
        }

        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            $this->executeBulkUpdate($table, $chunk, $primaryKey);
        }

        return count($data);
    }

    private function executeBulkUpdate($table, $data, $primaryKey)
    {
        $columns = array_keys($data[0]);
        $columns = array_filter($columns, fn($col) => $col !== $primaryKey);

        $cases = [];
        $ids = [];

        foreach ($data as $row) {
            $ids[] = $row[$primaryKey];

            foreach ($columns as $column) {
                $value = is_null($row[$column]) ? 'NULL' : "'" . addslashes($row[$column]) . "'";
                $cases[$column][] = "WHEN {$row[$primaryKey]} THEN {$value}";
            }
        }

        $setClauses = [];
        foreach ($columns as $column) {
            $setClauses[] = "`{$column}` = CASE `{$primaryKey}` " .
                           implode(' ', $cases[$column]) . " ELSE `{$column}` END";
        }

        $sql = "UPDATE `{$table}` SET " . implode(', ', $setClauses) .
               " WHERE `{$primaryKey}` IN (" . implode(',', $ids) . ")";

        DB::statement($sql);
    }
}

// Practical usage
class UserBatchService
{
    protected $bulkService;

    public function __construct(BulkInsertService $bulkService)
    {
        $this->bulkService = $bulkService;
    }

    public function updateUserPoints(array $userIds)
    {
        $updates = [];

        User::whereIn('id', $userIds)->chunk(1000, function ($users) use (&$updates) {
            foreach ($users as $user) {
                $updates[] = [
                    'id' => $user->id,
                    'points' => $user->calculatePoints(),
                    'last_points_update' => now()
                ];
            }
        });

        return $this->bulkService->bulkUpdate('users', $updates);
    }
}
```

### Optimized Sync for Many-to-Many Relations

```php
class OptimizedRelationSync
{
    public function syncManyToManyBulk($model, $relation, $attachData, $chunkSize = 1000)
    {
        $relationInstance = $model->{$relation}();
        $table = $relationInstance->getTable();
        $relatedKey = $relationInstance->getRelatedPivotKeyName();
        $parentKey = $relationInstance->getForeignPivotKeyName();

        // Remove existing relations
        $relationInstance->detach();

        // Prepare data for insertion
        $insertData = [];
        foreach ($attachData as $relatedId => $pivotData) {
            $row = [
                $parentKey => $model->getKey(),
                $relatedKey => $relatedId
            ];

            if (is_array($pivotData)) {
                $row = array_merge($row, $pivotData);
            }

            if ($relationInstance->withTimestamps()) {
                $row['created_at'] = now();
                $row['updated_at'] = now();
            }

            $insertData[] = $row;
        }

        // Insert in chunks
        $chunks = array_chunk($insertData, $chunkSize);
        foreach ($chunks as $chunk) {
            DB::table($table)->insert($chunk);
        }

        return count($insertData);
    }

    public function syncMultipleModels($modelClass, $relation, $syncData, $chunkSize = 1000)
    {
        foreach (array_chunk($syncData, $chunkSize, true) as $chunk) {
            DB::transaction(function () use ($modelClass, $relation, $chunk) {
                foreach ($chunk as $modelId => $attachData) {
                    $model = $modelClass::find($modelId);
                    if ($model) {
                        $model->{$relation}()->sync($attachData);
                    }
                }
            });
        }
    }
}

// Usage example: assign tags to posts
class PostTagService
{
    protected $relationSync;

    public function __construct(OptimizedRelationSync $relationSync)
    {
        $this->relationSync = $relationSync;
    }

    public function assignTagsToPosts(array $postTagAssignments)
    {
        // $postTagAssignments = [
        //     1 => [1, 2, 3], // Post 1 -> Tags 1, 2, 3
        //     2 => [2, 4, 5], // Post 2 -> Tags 2, 4, 5
        // ]

        $this->relationSync->syncMultipleModels(
            Post::class,
            'tags',
            $postTagAssignments,
            500
        );
    }
}
```

## Parallel Processing and Batch Jobs

### Optimized Queue Jobs

```php
class BulkProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $modelClass;
    protected $ids;
    protected $operation;
    protected $parameters;

    public function __construct($modelClass, array $ids, $operation, $parameters = [])
    {
        $this->modelClass = $modelClass;
        $this->ids = $ids;
        $this->operation = $operation;
        $this->parameters = $parameters;
    }

    public function handle()
    {
        $processed = 0;
        $errors = [];

        $this->modelClass::whereIn('id', $this->ids)
            ->chunkById(100, function ($items) use (&$processed, &$errors) {
                foreach ($items as $item) {
                    try {
                        $this->processItem($item);
                        $processed++;
                    } catch (\Exception $e) {
                        $errors[] = [
                            'id' => $item->id,
                            'error' => $e->getMessage()
                        ];
                    }
                }
            });

        // Save results
        BulkProcessResult::create([
            'job_id' => $this->job->getJobId(),
            'processed' => $processed,
            'errors' => $errors,
            'completed_at' => now()
        ]);
    }

    private function processItem($item)
    {
        switch ($this->operation) {
            case 'calculate_points':
                $item->calculateAndSaveLoyaltyPoints();
                break;

            case 'send_email':
                Mail::to($item->email)->send(new NotificationMail($this->parameters));
                break;

            case 'update_status':
                $item->update(['status' => $this->parameters['status']]);
                break;

            default:
                throw new \Exception("Unknown operation: {$this->operation}");
        }
    }
}

// Service to dispatch bulk jobs
class BulkJobDispatcher
{
    public function dispatchBulkOperation($modelClass, $operation, $parameters = [], $chunkSize = 1000)
    {
        $query = $this->buildQuery($modelClass, $parameters);
        $totalItems = $query->count();

        if ($totalItems === 0) {
            return ['message' => 'No items to process'];
        }

        $jobIds = [];

        $query->chunkById($chunkSize, function ($items) use ($modelClass, $operation, $parameters, &$jobIds) {
            $ids = $items->pluck('id')->toArray();

            $job = new BulkProcessJob($modelClass, $ids, $operation, $parameters);
            $jobIds[] = dispatch($job);
        });

        return [
            'message' => "Dispatched " . count($jobIds) . " jobs for {$totalItems} items",
            'job_ids' => $jobIds
        ];
    }

    private function buildQuery($modelClass, $parameters)
    {
        $query = $modelClass::query();

        if (isset($parameters['where'])) {
            foreach ($parameters['where'] as $condition) {
                $query->where($condition[0], $condition[1], $condition[2]);
            }
        }

        if (isset($parameters['ids'])) {
            $query->whereIn('id', $parameters['ids']);
        }

        return $query;
    }
}
```

### Batch Jobs with Progress Tracking

```php
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class BatchProcessingService
{
    public function processBatch($modelClass, $operation, $parameters = [], $chunkSize = 1000)
    {
        $query = $modelClass::query();
        $this->applyFilters($query, $parameters);

        $jobs = [];

        $query->chunkById($chunkSize, function ($items) use ($modelClass, $operation, $parameters, &$jobs) {
            $ids = $items->pluck('id')->toArray();
            $jobs[] = new BulkProcessJob($modelClass, $ids, $operation, $parameters);
        });

        $batch = Bus::batch($jobs)
            ->then(function (Batch $batch) {
                // All jobs completed successfully
                logger()->info('Batch processing completed successfully', [
                    'batch_id' => $batch->id,
                    'processed_jobs' => $batch->processedJobs()
                ]);
            })
            ->catch(function (Batch $batch, \Throwable $e) {
                // First job that failed
                logger()->error('Batch processing failed', [
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage()
                ]);
            })
            ->finally(function (Batch $batch) {
                // Batch completed (with or without success)
                $this->cleanupBatch($batch);
            })
            ->dispatch();

        return [
            'batch_id' => $batch->id,
            'total_jobs' => count($jobs),
            'estimated_time' => $this->estimateProcessingTime(count($jobs))
        ];
    }

    public function getBatchProgress($batchId)
    {
        $batch = Bus::findBatch($batchId);

        if (!$batch) {
            return ['error' => 'Batch not found'];
        }

        return [
            'id' => $batch->id,
            'total_jobs' => $batch->totalJobs,
            'processed_jobs' => $batch->processedJobs(),
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'progress' => round(($batch->processedJobs() / $batch->totalJobs) * 100, 1),
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled()
        ];
    }

    private function estimateProcessingTime($jobCount)
    {
        // Estimate based on history (average 2 seconds per job)
        $avgTimePerJob = 2;
        $concurrency = config('queue.connections.redis.workers', 3);

        return ceil(($jobCount * $avgTimePerJob) / $concurrency);
    }
}
```

## Use Case-Specific Optimizations

### Massive Data Migration

```php
class DataMigrationService
{
    public function migrateUserData($batchSize = 5000)
    {
        $totalUsers = DB::connection('old_db')->table('users')->count();
        $processed = 0;

        $this->info("Migrating {$totalUsers} users...");

        DB::connection('old_db')
            ->table('users')
            ->orderBy('id')
            ->chunk($batchSize, function ($oldUsers) use (&$processed, $totalUsers) {
                $newUsers = [];

                foreach ($oldUsers as $oldUser) {
                    $newUsers[] = $this->transformUser($oldUser);
                }

                // Batch insert into new DB
                DB::table('users')->insert($newUsers);

                $processed += count($newUsers);
                $percentage = round(($processed / $totalUsers) * 100, 1);

                $this->info("Progress: {$percentage}% ({$processed}/{$totalUsers})");
            });

        $this->info("Migration completed!");
    }

    private function transformUser($oldUser)
    {
        return [
            'id' => $oldUser->user_id,
            'name' => $oldUser->username,
            'email' => $oldUser->email_address,
            'email_verified_at' => $oldUser->verified ? now() : null,
            'created_at' => $oldUser->registration_date,
            'updated_at' => $oldUser->last_modified,
            // Transform old data to new format
            'profile' => json_encode([
                'bio' => $oldUser->biography,
                'website' => $oldUser->homepage,
                'location' => $oldUser->city . ', ' . $oldUser->country
            ])
        ];
    }
}
```

### Database Cleanup

```php
class DatabaseCleanupService
{
    public function cleanupOldData($daysToKeep = 90, $chunkSize = 10000)
    {
        $cutoffDate = now()->subDays($daysToKeep);

        $cleanupTasks = [
            'logs' => ['table' => 'logs', 'date_column' => 'created_at'],
            'sessions' => ['table' => 'sessions', 'date_column' => 'last_activity'],
            'notifications' => ['table' => 'notifications', 'date_column' => 'created_at', 'condition' => ['read_at', '!=', null]],
            'temp_files' => ['table' => 'temp_files', 'date_column' => 'created_at'],
        ];

        foreach ($cleanupTasks as $name => $config) {
            $this->info("Cleaning up {$name}...");
            $deleted = $this->cleanupTable($config, $cutoffDate, $chunkSize);
            $this->info("Deleted {$deleted} old {$name} records");
        }
    }

    private function cleanupTable($config, $cutoffDate, $chunkSize)
    {
        $totalDeleted = 0;

        do {
            $query = DB::table($config['table'])
                      ->where($config['date_column'], '<', $cutoffDate)
                      ->limit($chunkSize);

            // Add additional conditions if specified
            if (isset($config['condition'])) {
                $query->where($config['condition'][0], $config['condition'][1], $config['condition'][2]);
            }

            $deleted = $query->delete();
            $totalDeleted += $deleted;

            // Pause to avoid overloading the DB
            if ($deleted > 0) {
                usleep(100000); // 100ms pause
            }

        } while ($deleted > 0);

        return $totalDeleted;
    }
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Unit operations** are 100x slower than batch operations
2. **Intelligent chunking** with error handling prevents timeouts and failures
3. **Bulk operations** (INSERT, UPDATE, UPSERT) are essential for large volumes
4. **Batch queue jobs** enable parallel and asynchronous processing
5. **Progress tracking** improves user experience and monitoring

🛠️ **Techniques Mastered:**

- Safe chunking with error handling and rollback
- Optimized bulk insert, update, and upsert
- Parallel processing with Laravel Batches
- High-performance data migration
- Automated database cleanup

⚡ **Typical Performance Gains:**

- **Bulk INSERT**: 50x faster than unit INSERTs
- **Optimized chunking**: 10x reduction in processing time
- **Queue batches**: Parallel processing based on available workers
- **Progress tracking**: Better visibility without performance impact

🚀 **Action Items:**

- [ ] Identify your current batch operations
- [ ] Implement chunking on large datasets
- [ ] Replace unit operations with bulk operations
- [ ] Configure queue batches for asynchronous processing
- [ ] Add progress tracking to long operations

**In the next chapter, we'll explore advanced performance monitoring and debugging!**

---

# Chapter 10: Performance Monitoring and Debugging {#chapter-10}

## The Story of the Invisible Bug that Cost €50,000 per Month

In 2021, a French fintech had a mystery: their application worked normally on the surface, but their AWS bill was exploding every month. **€50,000** in unexplained overcharges on databases.

After 3 months of investigation, they discovered the culprit: **an innocent query** in a background job that ran every minute and generated 15,000 SQL queries each time. The worst part? This query was hidden in an Eloquent observer.

**The lesson:** The most expensive performance problems are often invisible. Without proper monitoring, you're navigating blind.

## Setting Up Complete Monitoring

### Laravel Telescope: Your Performance Dashboard

```php
// Installation and configuration
composer require laravel/telescope

php artisan telescope:install
php artisan migrate

// config/telescope.php - Optimized configuration
return [
    'enabled' => env('TELESCOPE_ENABLED', true),

    'storage' => [
        'database' => [
            'connection' => env('TELESCOPE_DB_CONNECTION', 'mysql'),
            'chunk' => 1000, // Process in chunks to avoid timeouts
        ],
    ],

    'watchers' => [
        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'slow' => 100, // Queries > 100ms considered slow
        ],

        Watchers\RequestWatcher::class => [
            'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
            'size_limit' => 64, // Limit payload size
        ],

        Watchers\JobWatcher::class => true,
        Watchers\ExceptionWatcher::class => true,
        Watchers\CacheWatcher::class => true,
    ],
];

// Intelligent data filtering
TelescopeServiceProvider::filter(function (IncomingEntry $entry) {
    if (app()->environment('local')) {
        return true;
    }

    return $entry->isReportableException() ||
           $entry->isFailedRequest() ||
           $entry->isFailedJob() ||
           ($entry->type === 'query' && $entry->content['time'] > 100);
});
```

### Debugbar: Real-Time Debugging

```php
// Installation
composer require barryvdh/laravel-debugbar --dev

// Configuration in config/debugbar.php
return [
    'enabled' => env('DEBUGBAR_ENABLED', null),
    'except' => [
        'telescope*',
        'horizon*',
    ],

    'collectors' => [
        'phpinfo'         => true,
        'messages'        => true,
        'time'           => true,
        'memory'         => true,
        'exceptions'     => true,
        'log'            => true,
        'db'             => true,
        'views'          => true,
        'route'          => true,
        'auth'           => false,
        'gate'           => true,
        'session'        => true,
        'symfony_request' => true,
        'mail'           => true,
        'laravel'        => false,
        'events'         => false,
        'default_request' => false,
        'logs'           => false,
        'files'          => false,
        'config'         => false,
        'cache'          => false,
    ],
];

// Custom middleware to capture metrics
class PerformanceDebugging
{
    public function handle($request, Closure $next)
    {
        if (!app()->environment('local')) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        $startQueries = count(DB::getQueryLog());

        DB::enableQueryLog();

        $response = $next($request);

        $executionTime = (microtime(true) - $startTime) * 1000; // in ms
        $memoryUsed = (memory_get_usage() - $startMemory) / 1024 / 1024; // in MB
        $queriesCount = count(DB::getQueryLog()) - $startQueries;

        // Add to headers for debugging
        $response->headers->set('X-Debug-Time', round($executionTime, 2) . 'ms');
        $response->headers->set('X-Debug-Memory', round($memoryUsed, 2) . 'MB');
        $response->headers->set('X-Debug-Queries', $queriesCount);

        // Log if performance is degraded
        if ($executionTime > 1000 || $queriesCount > 20) {
            logger()->warning('Slow request detected', [
                'url' => $request->fullUrl(),
```

## Lazy Collections: Stream Processing

### Processing Very Large Volumes

```php
// 🚀 To process millions of records without exploding memory
User::lazy() // Returns a LazyCollection
    ->filter(fn($user) => $user->orders->count() > 10)
    ->map(function ($user) {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'total_orders' => $user->orders->count()
        ];
    })
    ->chunk(1000)
    ->each(function ($chunk) {
        // Process each chunk of 1000 users
        $this->sendPromotionalEmail($chunk->toArray());
    });
```

### Lazy Collections with Relations

```php
// Data export with optimized relations
$csv = fopen('users_export.csv', 'w');

// Headers
fputcsv($csv, ['ID', 'Name', 'Email', 'Orders Count', 'Total Spent']);

User::with(['orders:user_id,total'])
    ->lazy()
    ->each(function ($user) use ($csv) {
        fputcsv($csv, [
            $user->id,
            $user->name,
            $user->email,
            $user->orders->count(),
            $user->orders->sum('total')
        ]);
    });

fclose($csv);
```

## Custom Pagination Techniques

### Load More Button (Infinite Scroll)

```php
class InfiniteScrollController extends Controller
{
    public function posts(Request $request)
    {
        $lastId = $request->get('last_id', 0);

        $posts = Post::with(['author:id,name', 'category:id,name'])
                    ->select(['id', 'title', 'excerpt', 'user_id', 'category_id'])
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->limit(20)
                    ->get();

        return response()->json([
            'posts' => $posts,
            'has_more' => $posts->count() === 20,
            'last_id' => $posts->last()?->id ?? $lastId
        ]);
    }
}

// Frontend side (JavaScript)
/*
let lastId = 0;
let loading = false;

async function loadMorePosts() {
    if (loading) return;
    loading = true;

    const response = await fetch(`/api/posts?last_id=${lastId}`);
    const data = await response.json();

    // Add posts to DOM
    data.posts.forEach(post => {
        document.getElementById('posts-container').appendChild(createPostElement(post));
    });

    lastId = data.last_id;

    if (!data.has_more) {
        document.getElementById('load-more-btn').style.display = 'none';
    }

    loading = false;
}
*/
```

### Pagination with Full-Text Search

```php
class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $page = $request->get('page', 1);

        if (strlen($query) < 3) {
            return response()->json(['results' => [], 'total' => 0]);
        }

        // Search with relevance score
        $posts = Post::selectRaw("
                posts.*,
                MATCH(title, content) AGAINST(? IN BOOLEAN MODE) as relevance_score
            ", [$query])
            ->whereRaw("MATCH(title, content) AGAINST(? IN BOOLEAN MODE)", [$query])
            ->with(['author:id,name'])
            ->orderBy('relevance_score', 'desc')
            ->paginate(15);

        return response()->json([
            'results' => $posts->items(),
            'total' => $posts->total(),
            'current_page' => $posts->currentPage(),
            'last_page' => $posts->lastPage()
        ]);
    }
}
```

## Specific Optimizations by Use Case

### E-commerce: Catalog with Filters

```php
class ProductPaginationService
{
    public function getPaginatedProducts(Request $request)
    {
        $query = Product::query();

        // Filters with index
        $this->applyFilters($query, $request);

        // Optimized sorting
        $this->applySorting($query, $request);

        // Adapted pagination
        return $this->paginateResults($query, $request);
    }

    private function applyFilters($query, $request)
    {
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_quantity', '>', 0);
        }
    }

    private function applySorting($query, $request)
    {
        $sortOptions = [
            'price_low' => ['price', 'asc'],
            'price_high' => ['price', 'desc'],
            'name' => ['name', 'asc'],
            'popular' => ['views', 'desc'],
            'newest' => ['created_at', 'desc']
        ];

        $sort = $request->get('sort', 'newest');

        if (isset($sortOptions[$sort])) {
            [$column, $direction] = $sortOptions[$sort];
            $query->orderBy($column, $direction);
        }
    }

    private function paginateResults($query, $request)
    {
        // Use cursor for large catalogs
        if ($request->boolean('use_cursor')) {
            return $query->cursorPaginate(24);
        }

        // Classic pagination for page navigation
        return $query->paginate(24);
    }
}
```

### Dashboard Analytics: Time Series Pagination

```php
class AnalyticsPagination
{
    public function getTimeSeriesData($metric, $startDate, $endDate, $granularity = 'day')
    {
        $query = Analytics::where('metric', $metric)
                         ->whereBetween('date', [$startDate, $endDate]);

        // Grouping according to granularity
        switch ($granularity) {
            case 'hour':
                $query->select([
                    DB::raw('DATE_FORMAT(date, "%Y-%m-%d %H:00:00") as period'),
                    DB::raw('SUM(value) as total')
                ]);
                break;

            case 'day':
                $query->select([
                    DB::raw('DATE(date) as period'),
                    DB::raw('SUM(value) as total')
                ]);
                break;

            case 'month':
                $query->select([
                    DB::raw('DATE_FORMAT(date, "%Y-%m-01") as period'),
                    DB::raw('SUM(value) as total')
                ]);
                break;
        }

        return $query->groupBy('period')
                    ->orderBy('period')
                    ->cursorPaginate(100); // Up to 100 points on the chart
    }
}
```

## Monitoring and Performance Debugging

### Pagination Monitoring Middleware

```php
class PaginationMonitoringMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Detect potentially slow paginations
        if ($request->has('page') && (int) $request->page > 100) {
            logger()->info('Large pagination detected', [
                'url' => $request->fullUrl(),
                'page' => $request->page,
                'user_id' => auth()->id()
            ]);
        }

        return $response;
    }
}
```

### Pagination Performance Tests

```php
// tests/Feature/PaginationPerformanceTest.php
class PaginationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagination_remains_fast_on_large_dataset()
    {
        // Create large dataset
        Post::factory(50000)->create();

        // Test different pages
        $pages = [1, 10, 100, 1000];

        foreach ($pages as $page) {
            $start = microtime(true);

            $response = $this->get("/posts?page={$page}");

            $executionTime = microtime(true) - $start;

            $response->assertOk();

            // No page should take more than 500ms
            $this->assertLessThan(0.5, $executionTime,
                "Page {$page} took {$executionTime}s"
            );
        }
    }

    public function test_cursor_pagination_performance()
    {
        Post::factory(10000)->create();

        $start = microtime(true);

        $response = $this->get('/api/posts/cursor');

        $executionTime = microtime(true) - $start;

        $response->assertOk();

        // Cursor pagination must be fast even on large volumes
        $this->assertLessThan(0.1, $executionTime);
    }
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Classic pagination** becomes slow with high OFFSET
2. **Cursor pagination** maintains constant performance
3. **Chunking** avoids memory issues on large volumes
4. **Lazy collections** enable stream processing
5. **ChunkById** avoids duplicates during concurrent modifications

🚀 **Techniques Mastered:**

- High-performance cursor pagination
- Secure chunking with error handling
- Lazy collections for mass processing
- Custom pagination (infinite scroll)
- Pagination performance monitoring

⚡ **Pagination Choice by Context:**

- **User interface**: Classic pagination (small collections)
- **Mobile API**: Cursor pagination or infinite scroll
- **Large volumes**: Cursor pagination mandatory
- **Data export**: Chunking + lazy collections
- **Batch processing**: ChunkById with transactions

🚀 **Action Items:**

- [ ] Identify your pages with pagination on large volumes
- [ ] Implement cursor pagination on tables > 10K records
- [ ] Replace `all()` processing with chunking
- [ ] Add monitoring to your paginations
- [ ] Test performance with realistic datasets

**In the next chapter, we'll explore intelligent caching strategies with Redis!**

---

# Chapter 7: Smart Caching with Redis {#chapter-7}

## The Story of the Startup That Saved 80% of Its Server Bill

In 2019, a social media startup had a problem: their database servers were constantly overloaded. Each user page generated 15-20 identical SQL queries. The AWS bill reached €15,000/month just for the database.

Their solution? **An intelligent caching strategy with Redis**. Result:

- **80% reduction** in SQL queries
- **85% reduction** in response times
- **70% savings** on the server bill
- **200% improvement** in user experience

The secret? They stopped seeing cache as a "bonus" and started treating it as a **critical data layer**.

## Laravel Cache Fundamentals

### Cache vs Database: Understanding the Difference

```php
// ❌ Without cache: 150ms per query
$user = User::with(['posts', 'profile'])->find($id);

// ✅ With cache: 5ms after first load
$user = Cache::remember("user.{$id}.full", 3600, function () use ($id) {
    return User::with(['posts', 'profile'])->find($id);
});
```

**Impact:** 3000% performance improvement!

### Optimized Redis Configuration

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache'),
    ],
],

// config/database.php - Optimized Redis connections
'redis' => [
    'client' => env('REDIS_CLIENT', 'predis'),
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_DB', '0'),
        'read_write_timeout' => 60,
        'tcp_keepalive' => 1,
    ],
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6379'),
        'database' => env('REDIS_CACHE_DB', '1'), // Separate database for cache
    ],
],
```

## Layered Cache Strategies

### Level 1: Model Cache

```php
// Reusable trait for all your models
trait Cacheable
{
    public static function findCached($id, $ttl = 3600)
    {
        $cacheKey = static::getCacheKey($id);

        return Cache::remember($cacheKey, $ttl, function () use ($id) {
            return static::find($id);
        });
    }

    public static function findWithRelationsCached($id, $relations = [], $ttl = 3600)
    {
        $cacheKey = static::getCacheKey($id, $relations);

        return Cache::remember($cacheKey, $ttl, function () use ($id, $relations) {
            return static::with($relations)->find($id);
        });
    }

    protected static function getCacheKey($id, $relations = [])
    {
        $model = strtolower(class_basename(static::class));
        $relationKey = empty($relations) ? '' : '.' . implode('.', $relations);
        return "{$model}.{$id}{$relationKey}";
    }

    // Automatic invalidation
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            $model->clearCache();
        });

        static::deleted(function ($model) {
            $model->clearCache();
        });
    }

    public function clearCache()
    {
        $pattern = static::getCacheKey($this->id) . '*';
        Cache::flush(); // In production, use Redis tags
    }
}

// Usage in your models
class User extends Model
{
    use Cacheable;
}

// In your controllers
$user = User::findCached(1);
$userWithPosts = User::findWithRelationsCached(1, ['posts'], 1800);
```

### Level 2: Complex Query Cache

```php
class PostService
{
    public function getPopularPosts($limit = 10, $ttl = 1800)
    {
        $cacheKey = "posts.popular.{$limit}";

        return Cache::remember($cacheKey, $ttl, function () use ($limit) {
            return Post::with(['author:id,name,avatar'])
                      ->withCount(['comments', 'likes'])
                      ->orderByRaw('(comments_count * 2 + likes_count) DESC')
                      ->limit($limit)
                      ->get();
        });
    }

    public function getCategoryPosts($categoryId, $page = 1, $perPage = 15)
    {
        $cacheKey = "posts.category.{$categoryId}.page.{$page}.{$perPage}";

        return Cache::remember($cacheKey, 900, function () use ($categoryId, $page, $perPage) {
            return Post::with(['author:id,name'])
                      ->where('category_id', $categoryId)
                      ->where('published', true)
                      ->orderBy('published_at', 'desc')
                      ->paginate($perPage, ['*'], 'page', $page);
        });
    }

    public function getPostWithRelatedData($slug)
    {
        $cacheKey = "post.full.{$slug}";

        return Cache::remember($cacheKey, 3600, function () use ($slug) {
            $post = Post::with([
                'author.profile',
                'category',
                'tags',
                'comments' => function ($query) {
                    $query->with('author:id,name,avatar')
                          ->where('approved', true)
                          ->latest()
                          ->limit(20);
                }
            ])->where('slug', $slug)->firstOrFail();

            // Also cache related posts
            $post->related_posts = Post::where('category_id', $post->category_id)
                                      ->where('id', '!=', $post->id)
                                      ->with('author:id,name')
                                      ->limit(5)
                                      ->get();

            return $post;
        });
    }
}
```

### Level 3: View and Fragment Cache

```php
class ViewCacheService
{
    public function getRenderedView($viewName, $data = [], $ttl = 3600)
    {
        $cacheKey = $this->getViewCacheKey($viewName, $data);

        return Cache::remember($cacheKey, $ttl, function () use ($viewName, $data) {
            return view($viewName, $data)->render();
        });
    }

    public function getCachedFragment($key, callable $callback, $ttl = 1800)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    private function getViewCacheKey($viewName, $data)
    {
        $dataKey = md5(serialize($data));
        return "view.{$viewName}.{$dataKey}";
    }
}

// In your controllers
class BlogController extends Controller
{
    protected $viewCache;

    public function __construct(ViewCacheService $viewCache)
    {
        $this->viewCache = $viewCache;
    }

    public function show(Post $post)
    {
        // Cache the complete page content
        $cachedContent = $this->viewCache->getRenderedView('blog.show', [
            'post' => $post,
            'relatedPosts' => $post->getRelatedPosts()
        ], 3600);

        return response($cachedContent);
    }
}
```

## Cache Tags: The Professional Solution

```php
// Install Redis driver with tag support
// composer require predis/predis

class TaggedCacheService
{
    public function cacheUserData($userId, $data, $ttl = 3600)
    {
        return Cache::tags(['users', "user.{$userId}"])
                   ->remember("user.{$userId}.data", $ttl, function () use ($data) {
                       return $data;
                   });
    }

    public function cachePostData($postId, $userId, $categoryId, $data, $ttl = 1800)
    {
        return Cache::tags([
                'posts',
                "post.{$postId}",
                "user.{$userId}",
                "category.{$categoryId}"
            ])
            ->remember("post.{$postId}.full", $ttl, function () use ($data) {
                return $data;
            });
    }

    public function invalidateUser($userId)
    {
        Cache::tags(["user.{$userId}"])->flush();
    }

    public function invalidateCategory($categoryId)
    {
        Cache::tags(["category.{$categoryId}"])->flush();
    }

    public function invalidateAllPosts()
    {
        Cache::tags(['posts'])->flush();
    }
}

// Observer for automatic invalidation
class PostObserver
{
    protected $taggedCache;

    public function __construct(TaggedCacheService $taggedCache)
    {
        $this->taggedCache = $taggedCache;
    }

    public function updated(Post $post)
    {
        // Invalidate all caches related to this post
        $this->taggedCache->invalidatePost($post->id);
        $this->taggedCache->invalidateUser($post->user_id);
        $this->taggedCache->invalidateCategory($post->category_id);
    }

    public function deleted(Post $post)
    {
        $this->taggedCache->invalidatePost($post->id);
        $this->taggedCache->invalidateUser($post->user_id);
        $this->taggedCache->invalidateCategory($post->category_id);
    }
}
```

## Distributed Cache and Advanced Strategies

### Write-Through Cache

```php
class WriteThroughCacheRepository
{
    protected $model;
    protected $cache;
    protected $ttl;

    public function __construct($model, $ttl = 3600)
    {
        $this->model = $model;
        $this->cache = Cache::store('redis');
        $this->ttl = $ttl;
    }

    public function find($id)
    {
        $cacheKey = $this->getCacheKey($id);

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // If not in cache, get from DB
        $model = $this->model::find($id);

        // Cache for next time
        if ($model) {
            $this->cache->put($cacheKey, $model, $this->ttl);
        }

        return $model;
    }

    public function update($id, $attributes)
    {
        // Update in database
        $model = $this->model::find($id);
        $model->update($attributes);

        // Update cache immediately
        $cacheKey = $this->getCacheKey($id);
        $this->cache->put($cacheKey, $model->fresh(), $this->ttl);

        return $model;
    }

    public function delete($id)
    {
        // Delete from database
        $result = $this->model::destroy($id);

        // Remove from cache
        $cacheKey = $this->getCacheKey($id);
        $this->cache->forget($cacheKey);

        return $result;
    }

    private function getCacheKey($id)
    {
        $modelName = strtolower(class_basename($this->model));
        return "{$modelName}.{$id}";
    }
}
```

### Automatic Cache Warming

```php
class CacheWarmingService
{
    protected $postService;
    protected $userService;

    public function __construct(PostService $postService, UserService $userService)
    {
        $this->postService = $postService;
        $this->userService = $userService;
    }

    public function warmEssentialCaches()
    {
        $this->warmHomepageCaches();
        $this->warmPopularContentCaches();
        $this->warmNavigationCaches();
    }

    private function warmHomepageCaches()
    {
        // Warm popular posts
        $this->postService->getPopularPosts(10);

        // Warm recent posts
        $this->postService->getRecentPosts(15);

        // Warm main categories
        Category::getCachedMainCategories();
    }

    private function warmPopularContentCaches()
    {
        // Identify most viewed posts
        $popularPostIds = Post::orderBy('views', 'desc')->limit(20)->pluck('id');

        foreach ($popularPostIds as $postId) {
            $post = Post::find($postId);
            if ($post) {
                $this->postService->getPostWithRelatedData($post->slug);
            }
        }
    }

    private function warmNavigationCaches()
    {
        // Warm menus
        Menu::getCachedMainMenu();

        // Warm sidebar widgets
        Widget::getCachedSidebarWidgets();
    }
}

// Artisan command for warming
class WarmCacheCommand extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Warm up essential caches';

    public function handle(CacheWarmingService $warmer)
    {
        $this->info('Warming caches...');

        $start = microtime(true);
        $warmer->warmEssentialCaches();
        $duration = round((microtime(true) - $start) * 1000);

        $this->info("Caches warmed in {$duration}ms");
    }
}
```

## Advanced Cache Patterns

### Cache-Aside Pattern

```php
class ProductService
{
    public function getProduct($id)
    {
        // 1. Try cache first
        $product = Cache::get("product.{$id}");

        if ($product === null) {
            // 2. If not in cache, go to database
            $product = Product::with(['category', 'images'])->find($id);

            if ($product) {
                // 3. Put in cache
                Cache::put("product.{$id}", $product, 3600);
            }
        }

        return $product;
    }

    public function updateProduct($id, $data)
    {
        // 1. Update database
        $product = Product::find($id);
        $product->update($data);

        // 2. Invalidate cache
        Cache::forget("product.{$id}");

        // 3. Optional: reload in cache immediately
        $this->getProduct($id);

        return $product;
    }
}
```

### Cache with Locks to Avoid Collisions

```php
class SafeCacheService
{
    public function getExpensiveData($key, callable $callback, $ttl = 3600)
    {
        // Try cache first
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        // Use lock to prevent multiple processes from executing
        // the same expensive query simultaneously
        $lockKey = "{$key}.lock";

        return Cache::lock($lockKey, 10)->get(function () use ($key, $callback, $ttl) {
            // Double-check: maybe another process already cached it
            $cached = Cache::get($key);
            if ($cached !== null) {
                return $cached;
            }

            // Execute expensive callback
            $data = $callback();

            // Put in cache
            Cache::put($key, $data, $ttl);

            return $data;
        });
    }
}

// Usage
$expensiveData = $safeCacheService->getExpensiveData('expensive.report', function () {
    return $this->generateComplexReport();
}, 7200);
```

### Circuit Breaker Pattern for Cache

```php
class CircuitBreakerCache
{
    private $failureThreshold = 5;
    private $timeoutThreshold = 30; // seconds
    private $recoveryTimeout = 60; // seconds

    public function get($key)
    {
        if ($this->isCircuitOpen()) {
            return null; // Circuit open, bypass cache
        }

        try {
            $start = microtime(true);
            $result = Cache::get($key);
            $duration = microtime(true) - $start;

            if ($duration > $this->timeoutThreshold) {
                $this->recordFailure();
            } else {
                $this->recordSuccess();
            }

            return $result;

        } catch (\Exception $e) {
            $this->recordFailure();
            return null;
        }
    }

    public function put($key, $value, $ttl)
    {
        if ($this->isCircuitOpen()) {
            return false;
        }

        try {
            Cache::put($key, $value, $ttl);
            $this->recordSuccess();
            return true;

        } catch (\Exception $e) {
            $this->recordFailure();
            return false;
        }
    }

    private function isCircuitOpen()
    {
        $failures = Cache::get('circuit.failures', 0);
        $lastFailure = Cache::get('circuit.last_failure');

        if ($failures >= $this->failureThreshold) {
            if ($lastFailure && (time() - $lastFailure) < $this->recoveryTimeout) {
                return true; // Circuit open
            } else {
                // Recovery time elapsed, reset
                $this->resetCircuit();
            }
        }

        return false;
    }

    private function recordFailure()
    {
        $failures = Cache::get('circuit.failures', 0) + 1;
        Cache::put('circuit.failures', $failures, 3600);
        Cache::put('circuit.last_failure', time(), 3600);
    }

    private function recordSuccess()
    {
        Cache::forget('circuit.failures');
        Cache::forget('circuit.last_failure');
    }

    private function resetCircuit()
    {
        Cache::forget('circuit.failures');
        Cache::forget('circuit.last_failure');
    }
}
```

## Cache Monitoring and Analytics

### Cache Hit Rate Monitoring

```php
class CacheAnalytics
{
    public function recordHit($key)
    {
        $date = now()->format('Y-m-d');
        Cache::increment("cache.hits.{$date}");
        Cache::increment("cache.hits.{$key}.{$date}");
    }

    public function recordMiss($key)
    {
        $date = now()->format('Y-m-d');
        Cache::increment("cache.misses.{$date}");
        Cache::increment("cache.misses.{$key}.{$date}");
    }

    public function getHitRate($date = null)
    {
        $date = $date ?? now()->format('Y-m-d');

        $hits = Cache::get("cache.hits.{$date}", 0);
        $misses = Cache::get("cache.misses.{$date}", 0);
        $total = $hits + $misses;

        if ($total === 0) {
            return 0;
        }

        return round(($hits / $total) * 100, 2);
    }

    public function getTopMissedKeys($date = null, $limit = 10)
    {
        $date = $date ?? now()->format('Y-m-d');
        $pattern = "cache.misses.*.{$date}";

        // Get all miss keys
        $keys = Cache::keys($pattern);
        $misses = [];

        foreach ($keys as $key) {
            $missCount = Cache::get($key, 0);
            $cacheKey = str_replace(['.misses.', ".{$date}"], '', $key);
            $misses[$cacheKey] = $missCount;
        }

        arsort($misses);
        return array_slice($misses, 0, $limit, true);
    }
}

// Middleware for automatic tracking
class CacheTrackingMiddleware
{
    protected $analytics;

    public function __construct(CacheAnalytics $analytics)
    {
        $this->analytics = $analytics;
    }

    public function handle($request, Closure $next)
    {
        // Override Cache facade for tracking
        $originalGet = Cache::getFacadeRoot();

        Cache::extend('tracked', function ($app) use ($originalGet) {
            return new TrackedCacheStore($originalGet, $this->analytics);
        });

        return $next($request);
    }
}
```

### Cache Dashboard

```php
class CacheDashboardController extends Controller
{
    protected $analytics;

    public function __construct(CacheAnalytics $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index()
    {
        $stats = [
            'hit_rate_today' => $this->analytics->getHitRate(),
            'hit_rate_yesterday' => $this->analytics->getHitRate(now()->subDay()->format('Y-m-d')),
            'top_missed_keys' => $this->analytics->getTopMissedKeys(),
            'cache_size' => $this->getCacheSize(),
            'memory_usage' => $this->getMemoryUsage()
        ];

        return view('admin.cache-dashboard', $stats);
    }

    public function clearCache(Request $request)
    {
        $type = $request->get('type', 'all');

        switch ($type) {
            case 'posts':
                Cache::tags(['posts'])->flush();
                break;
            case 'users':
                Cache::tags(['users'])->flush();
                break;
            case 'views':
                Cache::flush(); // In production, use more precise patterns
                break;
            default:
                Cache::flush();
        }

        return response()->json(['status' => 'success']);
    }

    private function getCacheSize()
    {
        // Using Redis CLI to get size
        $redis = Redis::connection('cache');
        return $redis->dbsize();
    }

    private function getMemoryUsage()
    {
        $redis = Redis::connection('cache');
        $info = $redis->info('memory');
        return $info['used_memory_human'];
    }
}
```

## Cache for Specific Applications

### E-commerce: Product and Price Caching

```php
class EcommerceCacheService
{
    public function getProductWithPricing($productId, $userId = null)
    {
        $baseKey = "product.{$productId}";
        $userKey = $userId ? "{$baseKey}.user.{$userId}" : $baseKey;

        return Cache::tags(['products', "product.{$productId}"])
                   ->remember($userKey, 1800, function () use ($productId, $userId) {
                       $product = Product::with([
                           'variants.pricing',
                           'images',
                           'category',
                           'brand'
                       ])->find($productId);

                       if ($product && $userId) {
                           // Personalized pricing for user
                           $user = User::find($userId);
                           $product->personalized_price = $this->calculatePersonalizedPrice($product, $user);
                       }

                       return $product;
                   });
    }

    public function getCategoryProducts($categoryId, $filters = [], $page = 1)
    {
        $filterKey = md5(serialize($filters));
        $cacheKey = "category.{$categoryId}.products.{$filterKey}.page.{$page}";

        return Cache::tags(['products', "category.{$categoryId}"])
                   ->remember($cacheKey, 900, function () use ($categoryId, $filters, $page) {
                       $query = Product::where('category_id', $categoryId)
                                     ->where('active', true);

                       // Apply filters
                       if (isset($filters['price_min'])) {
                           $query->where('price', '>=', $filters['price_min']);
                       }

                       if (isset($filters['price_max'])) {
                           $query->where('price', '<=', $filters['price_max']);
                       }

                       if (isset($filters['brand'])) {
                           $query->whereIn('brand_id', (array) $filters['brand']);
                       }

                       return $query->with(['images', 'brand'])
                                   ->paginate(24, ['*'], 'page', $page);
                   });
    }

    public function invalidateProduct($productId)
    {
        Cache::tags(["product.{$productId}"])->flush();
    }

    public function invalidateCategory($categoryId)
    {
        Cache::tags(["category.{$categoryId}"])->flush();
    }
}
```

### SaaS: Multi-tenant Cache

```php
class TenantCacheService
{
    protected $tenantId;

    public function __construct()
    {
        $this->tenantId = auth()->user()?->tenant_id ?? 'default';
    }

    public function getTenantData($key, callable $callback, $ttl = 3600)
    {
        $tenantKey = "tenant.{$this->tenantId}.{$key}";

        return Cache::tags(['tenants', "tenant.{$this->tenantId}"])
                   ->remember($tenantKey, $ttl, $callback);
    }

    public function getDashboardStats()
    {
        return $this->getTenantData('dashboard.stats', function () {
            return [
                'users_count' => User::where('tenant_id', $this->tenantId)->count(),
                'active_users' => User::where('tenant_id', $this->tenantId)
                                    ->where('last_login', '>=', now()->subDays(30))
                                    ->count(),
                'total_orders' => Order::where('tenant_id', $this->tenantId)->count(),
                'monthly_revenue' => Order::where('tenant_id', $this->tenantId)
                                         ->where('created_at', '>=', now()->startOfMonth())
                                         ->sum('total')
            ];
        }, 1800);
    }

    public function getUserPermissions($userId)
    {
        return $this->getTenantData("user.{$userId}.permissions", function () use ($userId) {
            return User::find($userId)->getAllPermissions();
        }, 3600);
    }

    public function invalidateTenant()
    {
        Cache::tags(["tenant.{$this->tenantId}"])->flush();
    }

    public function invalidateUser($userId)
    {
        Cache::forget("tenant.{$this->tenantId}.user.{$userId}.permissions");
    }
}
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Cache is not optional** - it's a critical data layer
2. **Redis with tags** enables granular and intelligent invalidation
3. **Cache warming** improves user experience
4. **Hit rate monitoring** helps optimize strategies
5. **Advanced patterns** (circuit breaker, locks) ensure reliability

🚀 **Techniques Mastered:**

- Multi-level caching (models, queries, views)
- Tagged cache for precise invalidation
- Write-through and cache-aside patterns
- Circuit breaker for resilience
- Cache analytics and monitoring

⚡ **Context-Specific Strategies:**

- **Blog/CMS**: Cache articles and static pages
- **E-commerce**: Product cache with fine invalidation
- **SaaS**: Multi-tenant cache with isolation
- **API**: Response cache with appropriate headers

🚀 **Action Items:**

- [ ] Configure Redis with tag support
- [ ] Implement cache on your most frequent queries
- [ ] Set up hit rate monitoring
- [ ] Create consistent invalidation strategy
- [ ] Implement cache warming for critical data

**In the next chapter, we'll explore complex relationships and their optimizations!**

---

# Chapter 8: Complex Relationships and Optimizations {#chapter-8}

## The Relationship that Crashed Amazon (Temporarily)

In 2018, Amazon had an incident with their recommendation system. The problem? A poorly optimized polymorphic relationship between products, reviews, and recommendations that generated **over 50,000 SQL queries** to display a single product page.

The incident lasted 3 hours and cost millions of dollars in lost sales. The solution? A complete refactoring of their relationship system with optimizations we'll explore in this chapter.

**The lesson:** Complex relationships can be your best asset or worst nightmare. It all depends on how you optimize them.

## Many-to-Many Relations: Beyond the Basics

### Common Problems and Solutions

```php
// ❌ PROBLEM: N+1 on many-to-many relationship
$posts = Post::limit(20)->get();
foreach ($posts as $post) {
    $tagNames = $post->tags->pluck('name')->implode(', '); // N+1!
}

// ✅ SOLUTION: Optimized eager loading
$posts = Post::with(['tags:id,name'])->limit(20)->get();
foreach ($posts as $post) {
    $tagNames = $post->tags->pluck('name')->implode(', ');
}
```

### Optimizing Pivot Tables

```php
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags')
                   ->withPivot(['created_at', 'created_by', 'weight'])
                   ->withTimestamps();
    }

    // Optimized relationship for display
    public function displayTags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags')
                   ->select(['tags.id', 'tags.name', 'tags.slug', 'tags.color'])
                   ->orderBy('tags.name');
    }

    // Weighted tags for algorithm
    public function weightedTags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags')
                   ->withPivot('weight')
                   ->orderBy('pivot_weight', 'desc');
    }

    // Optimization: count without loading
    public function getTagsCountAttribute()
    {
        return $this->tags()->count();
    }
}

// Optimized usage by context
class PostController extends Controller
{
    public function index()
    {
        // For listing: only tag names
        $posts = Post::with(['displayTags'])->paginate(15);
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        // For detailed view: tags with pivot information
        $post->load(['weightedTags']);
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        // For editing: all available tags + current tags
        $post->load(['tags:id,name']);
        $availableTags = Tag::select(['id', 'name'])->orderBy('name')->get();
        return view('posts.edit', compact('post', 'availableTags'));
    }
}
```

### Bulk Operations on Many-to-Many

```php
class TagService
{
    public function syncPostTags(Post $post, array $tagIds)
    {
        // ✅ Sync is more efficient than detach + attach
        return $post->tags()->sync($tagIds);
    }

    public function addTagsToMultiplePosts(array $postIds, array $tagIds)
    {
        $pivotData = [];

        foreach ($postIds as $postId) {
            foreach ($tagIds as $tagId) {
                $pivotData[] = [
                    'post_id' => $postId,
                    'tag_id' => $tagId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        // Batch insert is more efficient
        DB::table('post_tags')->insertOrIgnore($pivotData);
    }

    public function removeTagFromAllPosts($tagId)
    {
        // Batch deletion
        DB::table('post_tags')->where('tag_id', $tagId)->delete();
    }

    public function getPopularTags($limit = 20)
    {
        return Tag::select(['tags.*'])
                  ->join('post_tags', 'tags.id', '=', 'post_tags.tag_id')
                  ->groupBy('tags.id', 'tags.name')
                  ->orderByRaw('COUNT(post_tags.post_id) DESC')
                  ->limit($limit)
                  ->get();
    }
}
```

## Polymorphic Relations: Power and Pitfalls

### Optimized Polymorphic Comments

```php
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Optimized scope for approved comments
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    // Optimized loading by type
    public static function getOptimizedComments($commentableType, $commentableId)
    {
        return static::with(['author:id,name,avatar'])
                    ->where('commentable_type', $commentableType)
                    ->where('commentable_id', $commentableId)
                    ->whereNull('parent_id') // Only root comments
                    ->approved()
                    ->latest()
                    ->limit(20)
                    ->get();
    }
}

// Optimization in commentable models
class Post extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments()
    {
        return $this->morphMany(Comment::class, 'commentable')
                   ->where('approved', true)
                   ->whereNull('parent_id');
    }

    // Cached comment count
    public function getCommentsCountAttribute()
    {
        if (!isset($this->attributes['comments_count'])) {
            $this->attributes['comments_count'] = $this->comments()->count();
        }
        return $this->attributes['comments_count'];
    }
}

// Optimized loading with morphWith
class CommentService
{
    public function getCommentsWithCommentable($limit = 50)
    {
        return Comment::with(['commentable' => function (MorphTo $morphTo) {
                        $morphTo->morphWith([
                            Post::class => ['author:id,name'],
                            Video::class => ['channel:id,name'],
                            Product::class => ['category:id,name'],
                        ]);
                    }])
                    ->with(['author:id,name,avatar'])
                    ->approved()
                    ->latest()
                    ->limit($limit)
                    ->get();
    }
}
```

### Polymorphic Images with Optimizations

```php
class Image extends Model
{
    public function imageable()
    {
        return $this->morphTo();
    }

    // Optimized accessor for URLs
    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->path);
    }

    public function getThumbnailAttribute()
    {
        return Storage::disk('public')->url(str_replace('.jpg', '_thumb.jpg', $this->path));
    }
}

// Trait for models with images
trait HasImages
{
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function primaryImage()
    {
        return $this->morphOne(Image::class, 'imageable')
                   ->where('is_primary', true);
    }

    public function thumbnails()
    {
        return $this->morphMany(Image::class, 'imageable')
                   ->where('type', 'thumbnail');
    }

    // Optimized method to get primary image
    public function getPrimaryImageUrlAttribute()
    {
        if (!isset($this->_primary_image_url)) {
            $primaryImage = $this->primaryImage;
            $this->_primary_image_url = $primaryImage ? $primaryImage->url : '/default-image.jpg';
        }

        return $this->_primary_image_url;
    }
}

class Product extends Model
{
    use HasImages;

    // Override to optimize product image retrieval
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable')
                   ->select(['id', 'imageable_type', 'imageable_id', 'path', 'is_primary'])
                   ->orderBy('sort_order');
    }
}
```

## Has-One-Through and Has-Many-Through Relations

### Optimizing Distant Relations

```php
class Country extends Model
{
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }

    // Optimized relation with column selection
    public function recentPosts()
    {
        return $this->hasManyThrough(
            Post::class,
            User::class,
            'country_id', // Foreign key on users table
            'user_id',    // Foreign key on posts table
            'id',         // Local key on countries table
            'id'          // Local key on users table
        )
        ->select(['posts.id', 'posts.title', 'posts.created_at'])
        ->where('posts.published', true)
        ->latest('posts.created_at')
        ->limit(10);
    }
}

class User extends Model
{
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // Through relationship via profile
    public function latestOrder()
    {
        return $this->hasOneThrough(
            Order::class,
            Profile::class,
            'user_id',
            'profile_id',
            'id',
            'id'
        )->latest();
    }
}

// Optimized usage
class CountryStatsService
{
    public function getCountryWithStats($countryId)
    {
        return Country::with([
                'users:id,country_id,name',
                'recentPosts'
            ])
            ->withCount(['users', 'posts'])
            ->find($countryId);
    }

    public function getTopCountriesByPosts($limit = 10)
    {
        return Country::select(['id', 'name'])
                     ->withCount('posts')
                     ->orderBy('posts_count', 'desc')
                     ->limit($limit)
                     ->get();
    }
}
```

## Optimizing Conditional Relations

### Relations with Dynamic Conditions

```php
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->where('published', true);
    }

    public function recentPosts($days = 30)
    {
        return $this->hasMany(Post::class)
                   ->where('created_at', '>=', now()->subDays($days));
    }

    // Conditional relations with parameters
    public function postsByCategory($categoryId)
    {
        return $this->hasMany(Post::class)
                   ->where('category_id', $categoryId);
    }

    public function popularPosts($minViews = 1000)
    {
        return $this->hasMany(Post::class)
                   ->where('views', '>=', $minViews);
    }
}

// Service for optimized conditional loading
class UserPostService
{
    public function getUserWithPosts($userId, $filters = [])
    {
        $user = User::find($userId);

        // Dynamic construction of relations to load
        $relations = [];

        if (isset($filters['published_only']) && $filters['published_only']) {
            $relations['publishedPosts'] = function ($query) use ($filters) {
                $query->select(['id', 'user_id', 'title', 'created_at']);

                if (isset($filters['category'])) {
                    $query->where('category_id', $filters['category']);
                }

                $query->limit($filters['limit'] ?? 10);
            };
        } else {
            $relations['posts'] = function ($query) use ($filters) {
                $query->select(['id', 'user_id', 'title', 'published', 'created_at']);
                $query->limit($filters['limit'] ?? 10);
            };
        }

        return $user->load($relations);
    }
}
```

### Advanced withExists and withCount

```php
// Complex conditions with withExists
$users = User::withExists([
            'posts as has_recent_posts' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            },
            'posts as has_popular_posts' => function ($query) {
                $query->where('views', '>=', 1000);
            }
        ])
        ->withCount([
            'posts as total_posts_count',
            'posts as published_posts_count' => function ($query) {
                $query->where('published', true);
            },
            'posts as recent_posts_count' => function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            }
        ])
        ->get();

// In view, optimized usage
foreach ($users as $user) {
    if ($user->has_recent_posts) {
        echo "Active user with {$user->recent_posts_count} recent posts";
    }

    $publishedRatio = $user->total_posts_count > 0
        ? round(($user->published_posts_count / $user->total_posts_count) * 100)
        : 0;

    echo "Publication rate: {$publishedRatio}%";
}
```

## Optimized Self-Referencing Relations

### High-Performance Nested Comments

```php
class Comment extends Model
{
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Optimized method to load nested replies
    public function scopeWithReplies($query, $depth = 2)
    {
        $with = ['author:id,name,avatar'];

        // Dynamically build nested relations
        for ($i = 1; $i <= $depth; $i++) {
            $relation = str_repeat('replies.', $i);
            $with[] = $relation . 'author:id,name,avatar';
        }

        return $query->with($with);
    }

    // Alternative with single query for all comments
    public static function getThreadOptimized($postId, $depth = 3)
    {
        // Fetch all comments at once
        $allComments = static::with(['author:id,name,avatar'])
                            ->where('post_id', $postId)
                            ->orderBy('created_at')
                            ->get();

        // Organize into tree on PHP side (more efficient for large volumes)
        return static::buildCommentTree($allComments, null, $depth);
    }

    private static function buildCommentTree($comments, $parentId = null, $depth = 3, $currentDepth = 0)
    {
        if ($currentDepth >= $depth) {
            return collect();
        }

        return $comments->where('parent_id', $parentId)->map(function ($comment) use ($comments, $depth, $currentDepth) {
            $comment->replies = static::buildCommentTree($comments, $comment->id, $depth, $currentDepth + 1);
            return $comment;
        });
    }
}
```

### Hierarchical Categories

```php
class Category extends Model
{
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Get all descendants (optimized with CTE in MySQL 8+)
    public function getAllDescendants()
    {
        return DB::select("
            WITH RECURSIVE category_tree AS (
                SELECT id, name, parent_id, 0 as level
                FROM categories
                WHERE id = ?

                UNION ALL

                SELECT c.id, c.name, c.parent_id, ct.level + 1
                FROM categories c
                INNER JOIN category_tree ct ON c.parent_id = ct.id
                WHERE ct.level < 5
            )
            SELECT * FROM category_tree WHERE id != ?
        ", [$this->id, $this->id]);
    }

    // Alternative for MySQL < 8.0: Nested Set Model
    public function scopeDescendantsOf($query, $categoryId)
    {
        $category = static::find($categoryId);

        if (!$category) {
            return $query->whereRaw('1 = 0'); // Return empty query
        }

        return $query->where('lft', '>', $category->lft)
                    ->where('rgt', '<', $category->rgt);
    }

    // Optimized retrieval of categories with post counts
    public static function getHierarchyWithCounts()
    {
        return static::select(['id', 'name', 'parent_id', 'slug'])
                    ->with(['children' => function ($query) {
                        $query->withCount('posts');
                    }])
                    ->withCount('posts')
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->get();
    }
}
```

## Advanced Query Optimization for Relations

### Subquery Optimization

```php
// Instead of multiple queries, use subqueries
$users = User::select([
            'users.*',
            DB::raw('(
                SELECT COUNT(*)
                FROM posts
                WHERE posts.user_id = users.id
                AND posts.published = 1
            ) as published_posts_count'),
            DB::raw('(
                SELECT MAX(posts.created_at)
                FROM posts
                WHERE posts.user_id = users.id
            ) as last_post_date'),
            DB::raw('(
                SELECT AVG(posts.views)
                FROM posts
                WHERE posts.user_id = users.id
                AND posts.published = 1
            ) as avg_post_views')
        ])
        ->having('published_posts_count', '>', 0)
        ->orderBy('avg_post_views', 'desc')
        ->limit(20)
        ->get();
```

### Window Functions (MySQL 8+)

```php
// Use window functions for complex analysis
$postAnalytics = DB::select("
    SELECT
        p.id,
        p.title,
        p.views,
        u.name as author_name,
        c.name as category_name,
        ROW_NUMBER() OVER (PARTITION BY p.category_id ORDER BY p.views DESC) as rank_in_category,
        LAG(p.views) OVER (PARTITION BY p.user_id ORDER BY p.created_at) as previous_post_views,
        AVG(p.views) OVER (PARTITION BY p.user_id) as author_avg_views
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    WHERE p.published = 1
      AND p.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    ORDER BY p.views DESC
    LIMIT 100
");
```

## Chapter Summary

🎯 **Key Points to Remember:**

1. **Many-to-Many relations** require special attention for pivot tables
2. **Polymorphic relations** should use `morphWith()` to avoid N+1
3. **Has-Many-Through** can be very efficient to avoid multiple joins
4. **Conditional relations** enable targeted loading by context
5. **Self-referencing** requires special strategies for tree structures

🛠️ **Techniques Mastered:**

- Pivot table optimization with column selection
- Optimized polymorphic loading with morphWith
- Through relations to avoid complex joins
- withExists and withCount to avoid data loading
- Subqueries and window functions for advanced analysis

⚡ **Optimization Patterns:**

- **Targeted eager loading** by usage context
- **Bulk operations** for relationship modifications
- **Relation caching** for frequently used data
- **Subqueries** instead of multiple queries
- **Window functions** for statistical analysis

🚀 **Action Items:**

- [ ] Audit your most-used many-to-many relations
- [ ] Optimize polymorphic relations with morphWith
- [ ] Implement withCount/withExists where appropriate
- [ ] Test performance of your complex relations
- [ ] Consider window functions for your reports

**In the next chapter, we'll master batch operations and advanced chunking!**

---

# Chapter 9: Batch Operations and Advanced Chunking {#chapter-9}

## The Day Spotify Had to Process 50 Million Songs

In 2020, Spotify needed to migrate their music metadata database. **50 million songs** to process, with each song requiring: audio analysis, metadata, copyright information, and relationships with artists/albums.

Their first naive approach: process song by song. **Estimated time: 6 months**.

Their final solution with batch optimizations: **3 days**.

How did they do it? **Smart batch operations, parallel chunking, and optimized processing strategies** that we'll explore in this chapter.

## Understanding the Limits of Unit Operations

### The Hidden Cost of One-by-One Operations

```php
// ❌ SLOW: Unit processing
$users = User::all(); // 100,000 users
foreach ($users as $user) {
    $user->calculateLoyaltyPoints();
    $user->save(); // 100,000 UPDATE queries!
}

// Execution time: ~45 minutes
// Memory consumption: Can explode
// Timeout risk: High
```

### Impact of Different Approaches

```php
class BatchProcessingBenchmark extends Command
{
    public function handle()
    {
        $this->createTestData();

        $this->info('Testing different batch processing approaches...');

        // Approach 1: One by one
        $time1 = $this->testIndividualProcessing();

        // Approach 2: Basic chunking
        $time2 = $this->testBasicChunking();

        // Approach 3: Bulk operations
        $time3 = $this->testBulkOperations();

        // Approach 4: Chunking + Bulk
        $time4 = $this->testOptimizedChunking();

        $this->displayResults($time1, $time2, $time3, $time4);
    }

    private function testIndividualProcessing()
    {
        $start = microtime(true);

        User::chunk(1000, function ($users) {
            foreach ($users as $user) {
                $user->update(['points' => $user->calculatePoints()]);
            }
        });

        return microtime(true) - $start;
    }

    private function testBulkOperations()
    {
        $start = microtime(true);

        User::chunk(1000, function ($users) {
            $updates = [];
            foreach ($users as $user) {
                $updates[] = [
                    'id' => $user->id,
                    'points' => $user->calculatePoints()
                ];
            }

            // Bulk update
            $this->bulkUpdate('users', $updates, ['points']);
        });

        return microtime(true) - $start;
    }

    private function bulkUpdate($table, $data, $columns)
    {
        if (empty($data)) return;

        $cases = [];
        $ids = [];

        foreach ($data as $row) {
            $ids[] = $row['id'];
            foreach ($columns as $column) {
                $cases[$column][] = "WHEN {$row['id']} THEN {$row[$column]}";
            }
        }

        $caseSql = [];
        foreach ($columns as $column) {
            $caseSql[] = "`{$column}` = CASE `id` " . implode(' ', $cases[$column]) . " END";
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $caseSql) . " WHERE id IN (" . implode(',', $ids) . ")";

        DB::statement($sql);
    }
}
```

## Intelligent and Safe Chunking

### Chunking with Error Handling

```php
class SafeChunkProcessor
{
    protected $model;
    protected $chunkSize;
    protected $errors = [];
    protected $processed = 0;

    public function __construct($model, $chunkSize = 1000)
    {
        $this->model = $model;
        $this->chunkSize = $chunkSize;
    }

    public function process(callable $processor)
    {
        $this->model::chunkById($this->chunkSize, function ($chunk) use ($processor) {
            $this->processChunk($chunk, $processor);
        });

        return [
            'processed' => $this->processed,
            'errors' => $this->errors
        ];
    }

    private function processChunk($chunk, $processor)
    {
        DB::beginTransaction();

        try {
            // Process entire chunk
            foreach ($chunk as $item) {
                $processor($item);
                $this->processed++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();

            // If error on chunk, process individually
            $this->processSafely($chunk, $processor);
        }
    }

    private function processSafely($chunk, $processor)
    {
        foreach ($chunk as $item) {
            try {
                DB::transaction(function () use ($item, $processor) {
                    $processor($item);
                });

                $this->processed++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'id' => $item->id,
                    'error' => $e->getMessage(),
                    'data' => $item->toArray()
                ];
            }
        }
    }