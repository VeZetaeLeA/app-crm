---
name: ai-image-generation
description: Enables text-to-image, image-to-image, inpainting, and LoRA customization capabilities within the agent using the inference.sh CLI (infsh). Use this when the user asks to generate, edit, or upscale images, or when a task requires visual assets.
---

# AI Image Generation Skill

This skill enables you to generate and manipulate images using 50+ state-of-the-art AI models via the `inference.sh` platform.

## When to Use This Skill
Use this skill when the user:
- Asks to "generate an image of X"
- Wants to "edit this image" or "change X in this image"
- Asks for "a logo," "an icon," or "a background"
- Needs to "upscale" or "enhance" an existing image
- Requires "consistent characters" or "storyboard frames"
- Asks for "design inspiration" that requires visual output

## Prerequisites
- **infsh CLI**: Must be installed on the system.
- **Authentication**: You must be logged in (`infsh login`).

## Key Capabilities
- **Text-to-Image**: Generate images from text prompts.
- **Image-to-Image**: Use an existing image as a reference for generation.
- **Inpainting**: Edit specific parts of an image.
- **Upscaling**: Increase image resolution and detail.
- **LoRA Support**: Apply specific styles or characters using LoRAs.

## How to Use

### 1. Generating an Image (Text-to-Image)
To generate an image, use the `infsh app run` command with a model like FLUX or Gemini.

```bash
infsh app run falai/flux-dev --input '{"prompt": "a futuristic city with flying cars, cyberpunk style, high detail"}'
```

### 2. Available Models
The platform supports a wide range of models. Common ones include:
- `falai/flux-dev` - High-quality creative images.
- `falai/flux-realism` - Photorealistic images.
- `google/gemini-imagen-3` - Precision and adherence to complex prompts.
- `xai/grok-vision` - For vision-related tasks.

### 3. Image Editing (Inpainting/Outpainting)
To edit an image, provide the `image_url` and a `mask_url` or use a model designed for editing.

### 4. Character Consistency
Use the `character-design-sheet` skill if you need to maintain character consistency across multiple generations.

## Best Practices
- **Be Descriptive**: Include details about style, lighting, composition, and mood in prompts.
- **Negative Prompts**: Use them to exclude unwanted elements if the model supports it.
- **Aspect Ratio**: Specify desired dimensions (e.g., 1024x1024, 16:9).
- **Iteration**: Generate multiple versions and refine the prompt based on results.

## Troubleshooting
- **No output**: Check if `infsh` is logged in and has quota.
- **Poor quality**: Try a different model or refine the prompt with more technical keywords (e.g., "8k resolution," "unreal engine 5," "cinematic lighting").

---
*Powered by [inference.sh](https://inference.sh)*
