#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <dirent.h>
#include <sys/stat.h>
#include <ctype.h>

typedef struct {
    char *path;
    int line_count;
} FileResult;

typedef struct {
    FileResult *files;
    int size;
    int capacity;
} Category;

void init_category(Category *cat) {
    cat->size = 0;
    cat->capacity = 10;
    cat->files = malloc(cat->capacity * sizeof(FileResult));
}

void add_to_category(Category *cat, const char *path, int line_count) {
    if (cat->size >= cat->capacity) {
        cat->capacity *= 2;
        cat->files = realloc(cat->files, cat->capacity * sizeof(FileResult));
    }
    cat->files[cat->size].path = strdup(path);
    cat->files[cat->size].line_count = line_count;
    cat->size++;
}

int count_lines(const char *filename) {
    FILE *fp = fopen(filename, "r");
    if (fp == NULL) {
        return -1;
    }
    int count = 0;
    char ch;
    while ((ch = fgetc(fp)) != EOF) {
        if (ch == '\n') {
            count++;
        }
    }
    fclose(fp);
    return count;
}

void process_directory(const char *dir_path, int target, Category *cat_near, Category *cat_slight, Category *cat_heavy) {
    DIR *dir = opendir(dir_path);
    if (dir == NULL) {
        perror("opendir");
        return;
    }

    struct dirent *entry;
    while ((entry = readdir(dir)) != NULL) {
        // Ignorer . et ..
        if (strcmp(entry->d_name, ".") == 0 || strcmp(entry->d_name, "..") == 0) {
            continue;
        }

        char path[1024];
        snprintf(path, sizeof(path), "%s/%s", dir_path, entry->d_name);

        struct stat st;
        if (stat(path, &st) == 0) {
            if (S_ISDIR(st.st_mode)) {
                process_directory(path, target, cat_near, cat_slight, cat_heavy);
            } else if (S_ISREG(st.st_mode)) {
                int lines = count_lines(path);
                if (lines == -1) continue;

                double ratio = (double)lines / target;

                // Approchant la taille à 10% près (90% à 100%)
                if (ratio >= 0.9 && ratio < 1.0) {
                    add_to_category(cat_near, path, lines);
                }
                // Légèrement au-dessus (0 à 10% au-dessus, donc 100% à 110%)
                else if (ratio >= 1.0 && ratio <= 1.1) {
                    add_to_category(cat_slight, path, lines);
                }
                // Fortement au-dessus (> 110%)
                else if (ratio > 1.1) {
                    add_to_category(cat_heavy, path, lines);
                }
            }
        }
    }
    closedir(dir);
}

int main(int argc, char *argv[]) {
    if (argc < 3) {
        fprintf(stderr, "Usage: %s <target_lines> <directory>\n", argv[0]);
        return 1;
    }

    int target = atoi(argv[1]);
    char *dir_path = argv[2];

    Category cat_near, cat_slight, cat_heavy;
    init_category(&cat_near);
    init_category(&cat_slight);
    init_category(&cat_heavy);

    process_directory(dir_path, target, &cat_near, &cat_slight, &cat_heavy);

    printf("--- Résultats de l'analyse ---\n");
    printf("Cible : %d lignes\n\n", target);

    printf("1. Fichiers approchant la taille à 10%% près (90%%-100%%):\n");
    for (int i = 0; i < cat_near.size; i++) {
        printf("  - %s (%d lignes)\n", cat_near.files[i].path, cat_near.files[i].line_count);
    }
    if (cat_near.size == 0) printf("  (aucun)\n");

    printf("\n2. Fichiers légèrement au-dessus (0-10%% au-dessus):\n");
    for (int i = 0; i < cat_slight.size; i++) {
        printf("  - %s (%d lignes)\n", cat_slight.files[i].path, cat_slight.files[i].line_count);
    }
    if (cat_slight.size == 0) printf("  (aucun)\n");

    printf("\n3. Fichiers fortement au-dessus (>10%% au-dessus):\n");
    for (int i = 0; i < cat_heavy.size; i++) {
        printf("  - %s (%d lignes)\n", cat_heavy.files[i].path, cat_heavy.files[i].line_count);
    }
    if (cat_heavy.size == 0) printf("  (aucun)\n");

    // Libération de la mémoire (optionnel ici car fin du programme, mais propre)
    // ...

    return 0;
}
