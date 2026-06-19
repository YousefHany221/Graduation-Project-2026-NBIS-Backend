/**
* تسجيل طفل مفقود أو معثور عليه عبر الـ Nodes (Police / Nurse / Admin)
*/
public function registerFound(Request $request): JsonResponse
{
// 🎯 معالجة تضارب المسميات بين الفرونتيند والباكيند فورياً
if ($request->has('child_name') && !$request->has('name')) {
$request->merge(['name' => $request->input('child_name')]);
}
if ($request->has('child_name') && !$request->has('name_en')) {
$request->merge(['name_en' => $request->input('child_name')]);
}

$validated = $request->validate([
'name' => ['required', 'string', 'max:255'],
'name_en' => ['nullable', 'string', 'max:255'],
'mother_name' => ['nullable', 'string', 'max:255'],
'estimated_age' => ['nullable', 'string', 'max:50'],
'gender' => ['nullable', 'in:male,female'],
'found_location' => ['nullable', 'string', 'max:255'],
'notes' => ['nullable', 'string'],
'child_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
'footprint' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
]);

// معالجة وحفظ الصور المرفوعة
$childPhotoPath = null;
if ($request->hasFile('child_photo')) {
$childPhotoPath = $request->file('child_photo')->store('children/photos', 'public');
}

$footprintPath = null;
if ($request->hasFile('footprint')) {
$footprintPath = $request->file('footprint')->store('children/footprints', 'public');
}

// إنشاء سجل الطفل بالحالة الافتراضية المناسبة للبلاغ
$child = Child::create([
'name' => $validated['name'],
'name_en' => $validated['name_en'] ?? $validated['name'],
'mother_name' => $validated['mother_name'] ?? 'Unknown',
'estimated_age' => $validated['estimated_age'],
'gender' => $validated['gender'] ?? 'male',
'found_location' => $validated['found_location'],
'notes' => $validated['notes'],
'child_photo_path' => $childPhotoPath,
'footprint_path' => $footprintPath,
'status' => 'pending' // يبدأ كـ معلق تحت الفحص والمطابقة
]);

return response()->json([
'status' => 'success',
'message' => 'Infant record successfully injected into global registry.',
'data' => $child
], 201);
}