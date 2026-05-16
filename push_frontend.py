#!/usr/bin/env python3
import subprocess
import os
import sys

os.chdir(r'C:\Users\usuario\Downloads\WEBNIDA')

try:
    # Check current branch
    print("📍 Estado actual:")
    subprocess.run(['git', 'status', '--short'], check=True)
    
    # Checkout to WEBNIDA-FRONTEND branch
    print("\n🔀 Cambiando a rama WEBNIDA-FRONTEND...")
    subprocess.run(['git', 'checkout', 'WEBNIDA-FRONTEND'], check=True)
    
    # Add frontend changes
    print("\n📦 Añadiendo cambios del frontend...")
    subprocess.run(['git', 'add', 'frontend/'], check=True)
    
    # Check what will be committed
    print("\n📋 Cambios a ser confirmados:")
    subprocess.run(['git', 'diff', '--cached', '--name-only'], check=True)
    
    # Commit
    print("\n💾 Confirmando cambios...")
    result = subprocess.run(['git', 'commit', '-m', 'Actualización completa del frontend - WEBNIDA\n\nCo-authored-by: Copilot <223556219+Copilot@users.noreply.github.com>'], 
                          capture_output=True, text=True, check=False)
    if result.returncode == 0:
        print(result.stdout)
    else:
        print("⚠️  No hay cambios nuevos para confirmar")
    
    # Push to GitHub
    print("\n🚀 Subiendo a GitHub (rama WEBNIDA-FRONTEND)...")
    subprocess.run(['git', 'push', 'origin', 'WEBNIDA-FRONTEND'], check=True)
    
    # Show recent commits
    print("\n✅ ¡COMPLETADO! Últimos commits:")
    subprocess.run(['git', 'log', '--oneline', '-5'], check=True)
    
    print("\n🎉 Frontend subido exitosamente a GitHub")
    print("📄 URL: https://github.com/Nid1000/APPNI/tree/WEBNIDA-FRONTEND")
    
except subprocess.CalledProcessError as e:
    print(f"\n❌ Error: {e}")
    sys.exit(1)
